<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\Lead;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CmsSchemaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
    }

    public function test_content_casts_slug_uniqueness_and_lead_integrity(): void
    {
        $page = new Page;
        $page->title = 'Test';
        $page->slug = 'test';
        $page->template = 'standard';
        $page->sections = [['type' => 'hero']];
        $page->status = PublicationStatus::Published;
        $page->save();
        $this->assertSame(PublicationStatus::Published, $page->fresh()->status);
        $this->assertSame([['type' => 'hero']], $page->fresh()->sections);

        $translated = new Page;
        $translated->title = 'Test';
        $translated->slug = 'test';
        $translated->template = 'standard';
        $translated->locale = 'en';
        $translated->save();

        $service = new Service;
        $service->title = 'Test';
        $service->slug = 'service';
        $service->save();
        $lead = new Lead;
        $lead->name = 'Test';
        $lead->email = 'test@example.invalid';
        $lead->solution_type = 'service';
        $lead->service_id = $service->id;
        $lead->message = 'Test';
        $lead->privacy_accepted_at = now();
        $lead->save();
        $this->assertSame(LeadStatus::New, $lead->fresh()->status);
        try {
            $service->delete();
            $this->fail('A referenced service must not be deleted.');
        } catch (QueryException) {
            $this->assertNotNull($lead->fresh());
        }

        $this->expectException(QueryException::class);
        $duplicate = new Page;
        $duplicate->title = 'Duplicate';
        $duplicate->slug = 'test';
        $duplicate->template = 'standard';
        $duplicate->save();
    }

    public function test_existing_relationships_and_restrict_rules(): void
    {
        $category = new Category;
        $category->name = 'Test';
        $category->slug = 'test';
        $category->type = 'post';
        $category->save();

        $author = new User;
        $author->name = 'Test';
        $author->email = 'author@example.invalid';
        $author->password = 'synthetic-test-password';
        $author->save();

        $post = new Post;
        $post->title = 'Test';
        $post->slug = 'test';
        $post->author_id = $author->id;
        $post->category_id = $category->id;
        $post->save();
        $this->assertTrue($post->fresh()->author->is($author));
        $this->assertTrue($post->fresh()->category->is($category));

        $projectCategory = new Category;
        $projectCategory->name = 'Project';
        $projectCategory->slug = 'project';
        $projectCategory->type = 'project';
        $projectCategory->save();
        $project = new Project;
        $project->title = 'Test';
        $project->slug = 'test';
        $project->category_id = $projectCategory->id;
        $project->save();
        $this->assertTrue($project->fresh()->category->is($projectCategory));

        foreach ([$author, $category, $projectCategory] as $referenced) {
            try {
                $referenced->delete();
                $this->fail('Referenced records must not be deleted.');
            } catch (QueryException) {
                $this->assertTrue($referenced->fresh()->exists);
            }
        }
    }

    public function test_category_types_are_checked_on_model_saves(): void
    {
        $empty = new Category;
        $empty->name = 'Empty';
        $empty->slug = 'empty';
        $empty->type = 'post';
        $empty->save();
        $empty = $empty->fresh();
        $empty->name = 'Renamed';
        $empty->type = 'post';
        $empty->save();
        $this->assertSame('Renamed', $empty->fresh()->name);
        $postCategory = new Category;
        $postCategory->name = 'Post';
        $postCategory->slug = 'post';
        $postCategory->type = 'post';
        $postCategory->save();
        $projectCategory = new Category;
        $projectCategory->name = 'Project';
        $projectCategory->slug = 'project';
        $projectCategory->type = 'project';
        $projectCategory->save();
        $author = new User;
        $author->name = 'Test';
        $author->email = 'types@example.invalid';
        $author->password = 'synthetic-test-password';
        $author->save();

        $post = new Post;
        $post->title = 'Test';
        $post->slug = 'test';
        $post->author_id = $author->id;
        $post->save();
        $post->category_id = $postCategory->id;
        $post->save();
        $project = new Project;
        $project->title = 'Test';
        $project->slug = 'test';
        $project->save();
        $project->category_id = $projectCategory->id;
        $project->save();
        $this->assertNotNull($post->fresh()->category_id);
        $this->assertNotNull($project->fresh()->category_id);

        foreach ([[$post, 'category_id', $projectCategory->id], [$project, 'category_id', $postCategory->id], [$postCategory, 'type', 'project'], [$projectCategory, 'type', 'post'], [$projectCategory, 'type', 'invalid'], [$empty, 'type', 'project']] as [$model, $field, $value]) {
            $model->$field = $value;
            try {
                $model->save();
                $this->fail('Invalid category assignment must be rejected.');
            } catch (ValidationException) {
                $this->assertNotSame($value, $model->fresh()->$field);
            }
            $model->refresh();
        }
    }
}
