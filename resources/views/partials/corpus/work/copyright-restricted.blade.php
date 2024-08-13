<div class="w-full h-full flex flex-col items-center mt-10">
  <div>{!! __('corpus.work.copyright_no_content', ['target' => empty($target) ? $source->source_url : $target]) !!}</div>
  <div class="text-sans mt-20">{!! $source->source_content !!}</div>
</div>
