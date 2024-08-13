<canvas class="h-full w-full"
        x-init="chartConfig = {{ json_encode($chartData) }};  setTimeout(function () { new window.ChartJs($el.getContext('2d'), chartConfig); }, 1000)">
</canvas>
