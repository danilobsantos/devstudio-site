---
version: alpha
name: DevStudio
description: Sistema visual normativo; base de temas clara/escura aplicada localmente às views públicas, com aprovação visual ainda pendente.
colors:
  brand-500: "#D10047"
  brand-600: "#C8104A"
  brand-700: "#A80D3D"
  burgundy: "#851337"
  graphite: "#111315"
  surface-dark: "#17191E"
  surface-light: "#F6F6F7"
  text-primary: "#17171B"
  text-muted: "#717171"
  border: "#E5E7EB"
  white: "#FFFFFF"
typography:
  display:
    fontFamily: "Sora, system-ui, sans-serif"
    fontSize: 48px
    fontWeight: 600
    lineHeight: 1.1
    letterSpacing: -0.02em
  heading-lg:
    fontFamily: "Sora, system-ui, sans-serif"
    fontSize: 36px
    fontWeight: 600
    lineHeight: 1.2
  heading-md:
    fontFamily: "Sora, system-ui, sans-serif"
    fontSize: 28px
    fontWeight: 600
    lineHeight: 1.25
  heading-sm:
    fontFamily: "Sora, system-ui, sans-serif"
    fontSize: 22px
    fontWeight: 600
    lineHeight: 1.3
  body-lg:
    fontFamily: "Inter, system-ui, sans-serif"
    fontSize: 18px
    fontWeight: 400
    lineHeight: 1.6
  body-md:
    fontFamily: "Inter, system-ui, sans-serif"
    fontSize: 16px
    fontWeight: 400
    lineHeight: 1.5
  body-sm:
    fontFamily: "Inter, system-ui, sans-serif"
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.5
  label-md:
    fontFamily: "Inter, system-ui, sans-serif"
    fontSize: 14px
    fontWeight: 600
    lineHeight: 1.4
  label-sm:
    fontFamily: "Inter, system-ui, sans-serif"
    fontSize: 12px
    fontWeight: 600
    lineHeight: 1.4
spacing:
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  2xl: 48px
  3xl: 64px
rounded:
  sm: 4px
  md: 8px
  lg: 16px
components:
  button-primary:
    backgroundColor: "{colors.brand-600}"
    textColor: "{colors.white}"
    rounded: "{rounded.md}"
    padding: 12px
  card:
    backgroundColor: "{colors.white}"
    textColor: "{colors.text-primary}"
    rounded: "{rounded.md}"
    padding: 24px
---

# DevStudio — sistema visual

## Overview

Direção premium, minimalista, tecnológica e profissional, derivada da identidade existente. Superfícies claras predominam; fundos escuros podem organizar seções pontuais. Vermelho é assinatura e destaque, usado com parcimônia. Evitar neon, futurismo genérico, gradientes generalizados e animações que prejudiquem desempenho. A base local de temas em CSS atende páginas públicas existentes e usa preferência de sistema; ainda não é layout visual completo nem foi homologada. Outros tokens seguem proposta normativa, não aprovação de componentes.

T2.01 parcial: estão disponíveis somente as logos horizontais clara/escura e `Icone2.png`; `Icone3(2).png`, composição vertical citada na especificação, está ausente. Não redesenhar, substituir nem distorcer a marca. Uso de arquivos de Sora e Inter depende de confirmação de origem, licença e permissão de hospedagem; fontes não foram baixadas nem incluídas.

## Colors

Paleta de marca mantida conforme especificação. `text-muted` é o neutro ajustado proposto para atingir contraste mínimo sobre superfície clara; medir todos os usos reais, estados e superfícies antes da homologação.

| Token | Aplicação proposta |
|---|---|
| `brand-500` | Assinatura, links e destaques em superfície clara, se contraste adequado |
| `brand-600` | Ação primária; validar combinação de texto e estado |
| `brand-700` | Hover/realce escuro em fundo claro |
| `burgundy` | Detalhe escuro, não gradiente generalizado |
| `graphite`, `surface-dark` | Faixas e superfícies escuras |
| `surface-light`, `white` | Fundos claros e cartões |
| `text-primary` | Texto principal em claro |
| `text-muted` | Texto secundário em `surface-light`; não usar como única distinção |
| `border` | Divisores/bordas, validar distinção em cada fundo |

Contraste matemático sRGB aferido para pares selecionados: `#717171` sobre `#F6F6F7` = 4,52:1; `#17171B` sobre `#F6F6F7` = 16,55:1; branco sobre `#111315` = 18,62:1; branco sobre `#17191E` = 17,58:1. `#D10047` sobre `#17171B` = 3,24:1 e falha para texto normal. Referência WCAG 2.2 AA: 4,5:1 texto normal, 3:1 texto grande e componentes gráficos relevantes. Cálculo não é auditoria da interface.

T2.03 aplica nas views públicas estes pares sRGB: claro — `#17171B`/`#F6F6F7` 16,55:1, `#D10047`/`#F6F6F7` 5,11:1; escuro — `#F6F6F7`/`#17191E` 16,28:1, `#D10047`/`#17191E` 3,18:1 para foco gráfico (não texto). O modo system seleciona o tema via `prefers-color-scheme`; não há toggle. Valores são cálculo de cores planas, não validação visual final.

## Typography

Sora escolhida pelo fundador para títulos; Inter para texto corrido conforme especificação. Escala tipográfica é provisória e fluida deverá ser validada em interface. Não carregar fontes até confirmar licença, origem, pesos e política de entrega; usar fallback de sistema enquanto isso sem alegar equivalência. Respeitar hierarquia semântica, legibilidade, zoom e quebra de texto.

## Layout

Mobile-first. Espaçamento aprovado provisoriamente pelo fundador: escala de 4 a 64px em múltiplos definidos nos tokens. Adaptar largura e colunas ao conteúdo; evitar rolagem horizontal. Breakpoints finais dependem da validação responsiva; verificar viewport estreito, tablet, desktop e zoom.

## Elevation & Depth

Preferir superfícies e bordas discretas à sombra. Contraste entre planos deve manter hierarquia sem depender apenas da cor ou de efeitos. Evitar sombras decorativas generalizadas.

## Shapes

Raios 4px, 8px e 16px aprovados provisoriamente pelo fundador, associados a controles, cartões e superfícies maiores. Manter consistência dentro da mesma composição.

## Components

- Navegação/header: variante clara preferencial; versão escura somente com logo correspondente. Links, CTA, foco e menu móvel precisam de estados e leitura acessível.
- Botões: primário, secundário e link; definir repouso, hover, pressionado, foco visível, desabilitado e carregamento; não comunicar estado somente por cor.
- Cards: serviço, produto e projeto com título, resumo e ação clara; imagens somente quando autorizadas e disponíveis.
- Formulários: labels explícitos, instruções e erros associados, estados de envio/sucesso/falha e preservação de valores em erro.
- Conteúdo/FAQ: texto aprovado, sem HTML arbitrário; FAQ operável por teclado e estado anunciado.
- Estados de listagem: vazio/erro com orientação neutra, sem inventar conteúdo; omitir cases sem material autorizado.

## Do's and Don'ts

- Do preservar logos originais e selecionar a variante conforme o fundo; o arquivo vertical ainda falta.
- Do validar combinações de contraste para cada estado e fundo real antes de liberar interface.
- Do suportar teclado, foco visível não encoberto, estrutura semântica, texto redimensionável e `prefers-reduced-motion`; meta WCAG 2.2 AA depende de teste.
- Don't criar métricas, resultados, depoimentos, contatos ou materiais institucionais não confirmados.
- Don't redesenhar/substituir a marca ou carregar Sora/Inter antes da confirmação de licença e origem.
- Don't tratar este documento como evidência de UI, acessibilidade ou identidade implementada e aprovada.
