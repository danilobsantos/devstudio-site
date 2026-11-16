# Proposta visual — DevStudio

> **Rascunho para revisão.** Não é o `DESIGN.md` normativo nem aprova a interface. Base: [Design system atual](DESIGN-SYSTEM.md) e [especificação técnica, § 9](../ESPECIFICACAO_TECNICA_DEVSTUDIO_WEBSITE.md). Preserve a marca e a direção documentadas; valores novos abaixo são propostas, não decisões do fundador.

## Direção proposta

Interface institucional premium, minimalista e tecnológica, com apresentação editorial de soluções e produtos. Usar superfícies claras como base e seções escuras pontuais; vermelho é assinatura e acento de ação, não preenchimento decorativo recorrente. Priorizar hierarquia tipográfica, respiro e conteúdo verificável. Não usar estética neon/futurista genérica, gradientes em toda parte ou movimento que atrapalhe desempenho. Inspirações de estrutura, não de identidade: [Apple DESIGN.md](https://github.com/VoltAgent/awesome-design-md/blob/main/design-md/apple/DESIGN.md) pela contenção e alternância de superfícies; [Ferrari DESIGN.md](https://github.com/VoltAgent/awesome-design-md/blob/main/design-md/ferrari/DESIGN.md) pelo uso parcimonioso de vermelho. Não copiar suas cores, fontes proprietárias nem especificações de produto.

## Inventário de marca e T2.01

Inventário informado e conferido nos nomes/dimensões dos arquivos locais: `logo/logo_devstudio_dark_300x60.png` (300×60), `logo/logo_devstudio_light_300x60.png` (300×60), `logo/Icone2.png` (529×529). Há três artes disponíveis; `logo/Icone3(2).png`, composição vertical indicada na especificação, não foi encontrado. **T2.01 parcial**: preservar originais, usar cada logo horizontal conforme o fundo e não redesenhar, gerar ou substituir o símbolo. Solicitar o arquivo vertical original ou fonte vetorial ao responsável da marca. Não foram encontrados outros assets de arte nem fontes no projeto; metadados não validam aparência ou licença.

## Tokens e status das decisões

As cores de marca abaixo são copiadas da paleta inicial da especificação. Sora para títulos, espaçamento e raios foram aprovados provisoriamente; o texto secundário foi autorizado a escurecer para atingir o contraste mínimo. Escala tipográfica e outros tokens ainda são propostas; nenhum par de contraste certifica uma interface.

```yaml
colors:
  brand-500: "#D10047"
  brand-600: "#C8104A"
  brand-700: "#A80D3D"
  burgundy: "#851337"
  graphite: "#111315"
  surface-dark: "#17191E"
  surface-light: "#F6F6F7"
  text-primary: "#17171B"
  text-muted: "#717171" # proposta calculada para >=4.5:1 em surface-light
  border: "#E5E7EB"
  proposed-white: "#FFFFFF"
typography:
  heading-family: "Sora, system-ui, sans-serif" # escolha do fundador
  body-family: "Inter, system-ui, sans-serif" # licença/origem pendentes
  body: { fontSize: "1rem", fontWeight: 400, lineHeight: 1.5 }
  heading-scale: "fluid, values to validate" # escala provisória
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
  2xl: "48px"
  3xl: "64px"
rounded:
  control: "4px"
  card: "8px"
  section: "16px"
```

O fundador escolheu **Sora** para títulos e aprovou provisoriamente a escala de espaçamento de 4–64px e raios 4/8/16px. Corpo Inter conforme a fonte. Não foram baixadas nem incluídas fontes; origem, licença, hospedagem permitida, pesos e política de carregamento de Sora e Inter continuam pendentes. A base T2.03 usa somente `system-ui`, sem carregar ou declarar essas famílias; os pares efetivamente usados foram recalculados na raiz [DESIGN.md](../DESIGN.md).

## Contraste aferido (sRGB)

Razões calculadas matematicamente a partir dos hexadecimais, arredondadas a duas casas; não é auditoria da interface. Critérios de referência WCAG 2.2 AA: texto normal ≥4,5:1; texto grande ≥3:1; elementos gráficos e estados identificáveis ≥3:1.

| Primeiro plano / fundo | Razão | Leitura para texto normal |
|---|---:|---|
| `#D10047` / `#FFFFFF` | 5,52:1 | Passa |
| `#C8104A` / `#FFFFFF` | 5,79:1 | Passa |
| `#A80D3D` / `#FFFFFF` | 7,53:1 | Passa |
| `#D10047` / `#17171B` | 3,24:1 | Falha; apenas texto grande/elemento gráfico, sujeito a verificar |
| `#717171` / `#F6F6F7` | 4,52:1 | Passa; neutro autorizado para texto secundário |
| `#17171B` / `#F6F6F7` | 16,55:1 | Passa |
| `#FFFFFF` / `#111315` | 18,62:1 | Passa |
| `#FFFFFF` / `#17191E` | 17,58:1 | Passa |

`#717171` substitui o valor inicial `#737782` somente para texto secundário sobre `surface-light`, conforme autorização do fundador; preservar a paleta restante. O contraste aferido isoladamente não certifica combinações de estados, componentes ou texto sobre imagem. Testar hover, foco, desabilitado, contornos, transparência de imagens, fontes e fundos reais antes de declarar conformidade.

## Componentes iniciais e estados propostos

| Componente | Direção e estados a prototipar |
|---|---|
| Navegação/header | Fundo claro padrão; variante escura somente onde necessária, logo horizontal correspondente; links existentes identificáveis. CTA de contato depende de T4.07. Navegação responsiva operável por teclado; foco visível. |
| Botão primário | Vermelho de marca com texto de alto contraste; repouso, hover, pressionado, foco, desabilitado e carregamento. Verificar contraste de `brand-600`/texto branco e não sinalizar estado só por cor. |
| Botão secundário/link | Contorno ou texto sublinhado sobre superfície; foco bem delimitado; distinguir link de ação. Cor de contorno a validar. |
| Cards de serviço/produto/projeto | Superfície clara, borda discreta, título e resumo; imagem somente se autorizada e disponível. Estados repouso, foco e hover distinguíveis. Sem resultados, depoimentos ou métricas fictícias. |
| Formulário | Labels persistentes, instruções e erros associados aos campos, foco visível; estados inválido, enviando, sucesso e falha. Preservar valores em erro. |
| Footer | Identidade textual DevStudio e navegação aprovada; sem contatos, redes ou links institucionais ainda não aprovados. |
| Seções FAQ/CTA | Conteúdo aprovado; FAQ expansível acessível por teclado e estado anunciado; CTA sem número, destino ou promessa presumidos. |
| Listagem/estados vazios | Omitir cases sem autorização; vazio/erro com texto neutro e recuperação pertinente, sem conteúdo de exemplo apresentado como real. |

Aplicar layout mobile-first, sem rolagem horizontal, texto redimensionável, landmarks semânticos, hierarquia de headings, foco não encoberto, alvos adequados, `prefers-reduced-motion` e alternativa textual apropriada. Meta WCAG 2.2 AA conforme a fonte; requer validação independente em protótipo e interface final.

## Decisões e aprovação necessárias

- Fundador: Sora para títulos, espaçamento 4–64px e raios 4/8/16px aprovados provisoriamente; ajuste de `text-muted` para `#717171` autorizado. Escala tipográfica fluida e validação visual das variantes clara/escura seguem pendentes; base CSS/local para views existentes está implementada em T2.03.
- Responsável pela marca: fornecer `Icone3(2).png` original ou vetor e confirmar direitos/licenças dos arquivos de marca; licenças e origens das fontes Sora e Inter permanecem pendentes.
- Produto/conteúdo: validar os demais pares de contraste, estados dos componentes e copy institucional antes de publicação.
- Engenharia/design: validar tokens e estados em protótipo antes de ampliar UI. `DESIGN.md` na raiz registra o sistema visual normativo; sua existência ou a base CSS de T2.03 não significa interface homologada.

T2.03 adicionou uma base CSS temática e layout compartilhado às views públicas existentes, sem header/footer, troca manual, conteúdo novo ou migração de assets. QA aprovou a revalidação estática/funcional após correção de escopo CSS; inspeção visual em navegador não foi feita e homologação visual segue pendente. T2.02 ainda aguarda licença/origem das fontes e escala tipográfica final; este arquivo não marca a fase como concluída.
