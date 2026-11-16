<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (SiteSetting::query()->exists()) {
            return;
        }

        SiteSetting::create([
            'display_name' => 'DevStudio – Inovação Digital',
            'email' => 'contato@devstudio.com.br',
            'phone' => '+55 11 99999-9999',
            'whatsapp' => '5511999999999',
            'address' => 'São Paulo, SP — Brasil',
            'meta_title' => 'DevStudio – Inovação Digital | Tecnologia que Transforma Negócios',
            'meta_description' => 'Desenvolvemos softwares, sistemas empresariais e soluções digitais inteligentes para empresas que desejam evoluir, automatizar processos e crescer por meio da tecnologia.',
            'social_links' => [
                ['network' => 'linkedin', 'url' => 'https://linkedin.com/company/devstudio'],
                ['network' => 'github', 'url' => 'https://github.com/devstudio'],
                ['network' => 'instagram', 'url' => 'https://instagram.com/devstudio.digital'],
            ],
            'institutional_links' => [
                ['label' => 'Soluções', 'url' => '/solucoes'],
                ['label' => 'Produtos', 'url' => '/produtos'],
                ['label' => 'Sobre a Empresa', 'url' => '/sobre'],
                ['label' => 'Contato Comercial', 'url' => '/contato'],
            ],
        ]);
    }
}
