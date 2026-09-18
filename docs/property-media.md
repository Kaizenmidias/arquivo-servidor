# Mídia dos imóveis

As imagens são convertidas em três versões WebP. Depois que todas existem e têm conteúdo, o arquivo original é apagado. Um upload que não possa ser vinculado ao imóvel gera erro de validação. Os trabalhos de conversão são enviados após a transação do banco.

Os vídeos enviados no cadastro ou na edição do imóvel são guardados temporariamente em `storage/app/private/properties/videos/original`. O worker converte o arquivo em WebM com VP9/Opus, largura máxima de 1280 pixels e CRF 36. O original é removido somente depois que o WebM passa na validação e o registro aponta para ele. Se a conversão falhar, o original permanece para diagnóstico ou nova tentativa.

O servidor precisa de `ffmpeg` com `libvpx-vp9` e `libopus`, `ffprobe`, worker de fila em execução e `DB_QUEUE_RETRY_AFTER` maior que o limite de 1800 segundos do trabalho de vídeo. O valor padrão do projeto é 1900 segundos. Para arquivos de até 200 MB, configure também `upload_max_filesize`, `post_max_size` e o limite de corpo do proxy/web server acima desse tamanho. O agendador do Laravel deve executar `schedule:run` a cada minuto para limpar uploads abandonados após dois dias.

Comandos de limpeza de originais antigos, após o deploy:

```bash
php83 artisan properties:prune-original-images
php83 artisan properties:prune-original-images --execute
php83 artisan properties:prune-original-videos
php83 artisan properties:prune-original-videos --execute
```

Os comandos sem `--execute` apenas calculam o espaço recuperável. A limpeza de uploads não vinculados pode ser executada manualmente com `php83 artisan properties:prune-staged-media`.
