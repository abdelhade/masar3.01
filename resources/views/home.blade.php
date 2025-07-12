@extends('admin.dashboard')

@section('content')
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card p-3 shadow d-flex flex-row align-items-center">
                <div class="me-3">
                    <i class="fas fa-box fa-2x text-primary"></i>
                </div>
                <div>
                    <h6 class="mb-1">عدد الأصناف</h6>
                    <h4>{{ $totalItems }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow d-flex flex-row align-items-center">
                <div class="me-3">
                    <i class="fas fa-users fa-2x text-success"></i>
                </div>
                <div>
                    <h6 class="mb-1">عدد المستخدمين</h6>
                    <h4>{{ $totalUsers }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow d-flex flex-row align-items-center">
                <div class="me-3">
                    <i class="fas fa-file-invoice-dollar fa-2x text-warning"></i>
                </div>
                <div>
                    <h6 class="mb-1">عدد السندات</h6>
                    <h4>{{ $totalVouchers }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow d-flex flex-row align-items-center">
                <div class="me-3">
                    <i class="fas fa-user-tie fa-2x text-danger"></i>
                </div>
                <div>
                    <h6 class="mb-1">عدد الموظفين</h6>
                    <h4>{{ $totalEmployees }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">المعاملات النقديه</h4>
                </div><!--end card-header-->
                <div class="card-body">
                    <div class="chart-demo" style="position: relative;">
                        <div id="apex_column2" class="apex-charts" style="min-height: 395px;">
                            <div id="apexcharts51wc6w3tg"
                                class="apexcharts-canvas apexcharts51wc6w3tg apexcharts-theme-light"
                                style="width: 780px; height: 380px;"><svg id="SvgjsSvg3927" width="780" height="380"
                                    xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs"
                                    class="apexcharts-svg" xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                    style="background: transparent;">
                                    <g id="SvgjsG3929" class="apexcharts-inner apexcharts-graphical"
                                        transform="translate(22, 41.652)">
                                        <defs id="SvgjsDefs3928">
                                            <linearGradient id="SvgjsLinearGradient3933" x1="0" y1="0"
                                                x2="0" y2="1">
                                                <stop id="SvgjsStop3934" stop-opacity="0.4"
                                                    stop-color="rgba(216,227,240,0.4)" offset="0"></stop>
                                                <stop id="SvgjsStop3935" stop-opacity="0.5"
                                                    stop-color="rgba(190,209,230,0.5)" offset="1"></stop>
                                                <stop id="SvgjsStop3936" stop-opacity="0.5"
                                                    stop-color="rgba(190,209,230,0.5)" offset="1"></stop>
                                            </linearGradient>
                                            <clipPath id="gridRectMask51wc6w3tg">
                                                <rect id="SvgjsRect3938" width="752" height="312.348" x="-2" y="0"
                                                    rx="0" ry="0" opacity="1" stroke-width="0"
                                                    stroke="none" stroke-dasharray="0" fill="#fff"></rect>
                                            </clipPath>
                                            <clipPath id="gridRectMarkerMask51wc6w3tg">
                                                <rect id="SvgjsRect3939" width="752" height="316.348" x="-2" y="-2"
                                                    rx="0" ry="0" opacity="1" stroke-width="0"
                                                    stroke="none" stroke-dasharray="0" fill="#fff"></rect>
                                            </clipPath>
                                            <filter id="SvgjsFilter5833" filterUnits="userSpaceOnUse" width="200%"
                                                height="200%" x="-50%" y="-50%">
                                                <feComponentTransfer id="SvgjsFeComponentTransfer5834"
                                                    result="SvgjsFeComponentTransfer5834Out" in="SourceGraphic">
                                                    <feFuncR id="SvgjsFeFuncR5835" type="linear" slope="0.65"></feFuncR>
                                                    <feFuncG id="SvgjsFeFuncG5836" type="linear" slope="0.65">
                                                    </feFuncG>
                                                    <feFuncB id="SvgjsFeFuncB5837" type="linear" slope="0.65">
                                                    </feFuncB>
                                                    <feFuncA id="SvgjsFeFuncA5838" type="identity"></feFuncA>
                                                </feComponentTransfer>
                                            </filter>
                                        </defs>
                                        <rect id="SvgjsRect3937" width="43.63333333333334" height="312.348"
                                            x="195.66671142578124" y="0" rx="0" ry="0" opacity="1"
                                            stroke-width="0" stroke-dasharray="3" fill="url(#SvgjsLinearGradient3933)"
                                            class="apexcharts-xcrosshairs" y2="312.348" filter="none"
                                            fill-opacity="0.9" x1="195.66671142578124" x2="195.66671142578124"></rect>
                                        <g id="SvgjsG4004" class="apexcharts-xaxis" transform="translate(0, 0)">
                                            <g id="SvgjsG4005" class="apexcharts-xaxis-texts-g"
                                                transform="translate(0, -22)"><text id="SvgjsText4007"
                                                    font-family="Helvetica, Arial, sans-serif" x="31.166666666666668"
                                                    y="-26.652" text-anchor="middle" dominant-baseline="auto"
                                                    font-size="12px" font-weight="400" fill="#373d3f"
                                                    class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4008">Jan</tspan>
                                                    <title>Jan</title>
                                                </text><text id="SvgjsText4010" font-family="Helvetica, Arial, sans-serif"
                                                    x="93.5" y="-26.652" text-anchor="middle" dominant-baseline="auto"
                                                    font-size="12px" font-weight="400" fill="#373d3f"
                                                    class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4011">Feb</tspan>
                                                    <title>Feb</title>
                                                </text><text id="SvgjsText4013" font-family="Helvetica, Arial, sans-serif"
                                                    x="155.83333333333334" y="-26.652" text-anchor="middle"
                                                    dominant-baseline="auto" font-size="12px" font-weight="400"
                                                    fill="#373d3f" class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4014">Mar</tspan>
                                                    <title>Mar</title>
                                                </text><text id="SvgjsText4016" font-family="Helvetica, Arial, sans-serif"
                                                    x="218.16666666666669" y="-26.652" text-anchor="middle"
                                                    dominant-baseline="auto" font-size="12px" font-weight="400"
                                                    fill="#373d3f" class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4017">Apr</tspan>
                                                    <title>Apr</title>
                                                </text><text id="SvgjsText4019" font-family="Helvetica, Arial, sans-serif"
                                                    x="280.5" y="-26.652" text-anchor="middle" dominant-baseline="auto"
                                                    font-size="12px" font-weight="400" fill="#373d3f"
                                                    class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4020">May</tspan>
                                                    <title>May</title>
                                                </text><text id="SvgjsText4022" font-family="Helvetica, Arial, sans-serif"
                                                    x="342.8333333333333" y="-26.652" text-anchor="middle"
                                                    dominant-baseline="auto" font-size="12px" font-weight="400"
                                                    fill="#373d3f" class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4023">Jun</tspan>
                                                    <title>Jun</title>
                                                </text><text id="SvgjsText4025" font-family="Helvetica, Arial, sans-serif"
                                                    x="405.16666666666663" y="-26.652" text-anchor="middle"
                                                    dominant-baseline="auto" font-size="12px" font-weight="400"
                                                    fill="#373d3f" class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4026">Jul</tspan>
                                                    <title>Jul</title>
                                                </text><text id="SvgjsText4028" font-family="Helvetica, Arial, sans-serif"
                                                    x="467.49999999999994" y="-26.652" text-anchor="middle"
                                                    dominant-baseline="auto" font-size="12px" font-weight="400"
                                                    fill="#373d3f" class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4029">Aug</tspan>
                                                    <title>Aug</title>
                                                </text><text id="SvgjsText4031" font-family="Helvetica, Arial, sans-serif"
                                                    x="529.8333333333334" y="-26.652" text-anchor="middle"
                                                    dominant-baseline="auto" font-size="12px" font-weight="400"
                                                    fill="#373d3f" class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4032">Sep</tspan>
                                                    <title>Sep</title>
                                                </text><text id="SvgjsText4034" font-family="Helvetica, Arial, sans-serif"
                                                    x="592.1666666666667" y="-26.652" text-anchor="middle"
                                                    dominant-baseline="auto" font-size="12px" font-weight="400"
                                                    fill="#373d3f" class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4035">Oct</tspan>
                                                    <title>Oct</title>
                                                </text><text id="SvgjsText4037" font-family="Helvetica, Arial, sans-serif"
                                                    x="654.5000000000001" y="-26.652" text-anchor="middle"
                                                    dominant-baseline="auto" font-size="12px" font-weight="400"
                                                    fill="#373d3f" class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4038">Nov</tspan>
                                                    <title>Nov</title>
                                                </text><text id="SvgjsText4040" font-family="Helvetica, Arial, sans-serif"
                                                    x="716.8333333333335" y="-26.652" text-anchor="middle"
                                                    dominant-baseline="auto" font-size="12px" font-weight="400"
                                                    fill="#373d3f" class="apexcharts-text apexcharts-xaxis-label "
                                                    style="font-family: Helvetica, Arial, sans-serif;">
                                                    <tspan id="SvgjsTspan4041">Dec</tspan>
                                                    <title>Dec</title>
                                                </text></g>
                                            <line id="SvgjsLine4042" x1="0" y1="0" x2="748"
                                                y2="0" stroke="#28365f" stroke-dasharray="0" stroke-width="1">
                                            </line>
                                        </g>
                                        <g id="SvgjsG4045" class="apexcharts-grid">
                                            <g id="SvgjsG4046" class="apexcharts-gridlines-horizontal">
                                                <line id="SvgjsLine4061" x1="0" y1="0" x2="748"
                                                    y2="0" stroke="#f1f3fa" stroke-dasharray="0"
                                                    class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine4062" x1="0" y1="78.087" x2="748"
                                                    y2="78.087" stroke="#f1f3fa" stroke-dasharray="0"
                                                    class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine4063" x1="0" y1="156.174" x2="748"
                                                    y2="156.174" stroke="#f1f3fa" stroke-dasharray="0"
                                                    class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine4064" x1="0" y1="234.26100000000002"
                                                    x2="748" y2="234.26100000000002" stroke="#f1f3fa"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine4065" x1="0" y1="312.348" x2="748"
                                                    y2="312.348" stroke="#f1f3fa" stroke-dasharray="0"
                                                    class="apexcharts-gridline"></line>
                                            </g>
                                            <g id="SvgjsG4047" class="apexcharts-gridlines-vertical"></g>
                                            <line id="SvgjsLine4048" x1="0" y1="0" x2="0"
                                                y2="-6" stroke="#28365f" stroke-dasharray="0"
                                                class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4049" x1="62.333333333333336" y1="0"
                                                x2="62.333333333333336" y2="-6" stroke="#28365f"
                                                stroke-dasharray="0" class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4050" x1="124.66666666666667" y1="0"
                                                x2="124.66666666666667" y2="-6" stroke="#28365f"
                                                stroke-dasharray="0" class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4051" x1="187" y1="0" x2="187"
                                                y2="-6" stroke="#28365f" stroke-dasharray="0"
                                                class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4052" x1="249.33333333333334" y1="0"
                                                x2="249.33333333333334" y2="-6" stroke="#28365f"
                                                stroke-dasharray="0" class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4053" x1="311.6666666666667" y1="0"
                                                x2="311.6666666666667" y2="-6" stroke="#28365f"
                                                stroke-dasharray="0" class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4054" x1="374" y1="0" x2="374"
                                                y2="-6" stroke="#28365f" stroke-dasharray="0"
                                                class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4055" x1="436.3333333333333" y1="0"
                                                x2="436.3333333333333" y2="-6" stroke="#28365f"
                                                stroke-dasharray="0" class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4056" x1="498.66666666666663" y1="0"
                                                x2="498.66666666666663" y2="-6" stroke="#28365f"
                                                stroke-dasharray="0" class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4057" x1="561" y1="0" x2="561"
                                                y2="-6" stroke="#28365f" stroke-dasharray="0"
                                                class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4058" x1="623.3333333333334" y1="0"
                                                x2="623.3333333333334" y2="-6" stroke="#28365f"
                                                stroke-dasharray="0" class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4059" x1="685.6666666666667" y1="0"
                                                x2="685.6666666666667" y2="-6" stroke="#28365f"
                                                stroke-dasharray="0" class="apexcharts-xaxis-tick"></line>
                                            <line id="SvgjsLine4060" x1="748.0000000000001" y1="0"
                                                x2="748.0000000000001" y2="-6" stroke="#28365f"
                                                stroke-dasharray="0" class="apexcharts-xaxis-tick"></line>
                                            <rect id="SvgjsRect4066" width="748" height="78.087" x="0" y="0"
                                                rx="0" ry="0" opacity="0.2" stroke-width="0"
                                                stroke="none" stroke-dasharray="0" fill="#afb7d21a"
                                                clip-path="url(#gridRectMask51wc6w3tg)" class="apexcharts-grid-row">
                                            </rect>
                                            <rect id="SvgjsRect4067" width="748" height="78.087" x="0" y="78.087"
                                                rx="0" ry="0" opacity="0.2" stroke-width="0"
                                                stroke="none" stroke-dasharray="0" fill="transparent"
                                                clip-path="url(#gridRectMask51wc6w3tg)" class="apexcharts-grid-row">
                                            </rect>
                                            <rect id="SvgjsRect4068" width="748" height="78.087" x="0" y="156.174"
                                                rx="0" ry="0" opacity="0.2" stroke-width="0"
                                                stroke="none" stroke-dasharray="0" fill="#afb7d21a"
                                                clip-path="url(#gridRectMask51wc6w3tg)" class="apexcharts-grid-row">
                                            </rect>
                                            <rect id="SvgjsRect4069" width="748" height="78.087" x="0"
                                                y="234.26100000000002" rx="0" ry="0" opacity="0.2"
                                                stroke-width="0" stroke="none" stroke-dasharray="0" fill="transparent"
                                                clip-path="url(#gridRectMask51wc6w3tg)" class="apexcharts-grid-row">
                                            </rect>
                                            <line id="SvgjsLine4071" x1="0" y1="312.348" x2="748"
                                                y2="312.348" stroke="transparent" stroke-dasharray="0"></line>
                                            <line id="SvgjsLine4070" x1="0" y1="1" x2="0"
                                                y2="312.348" stroke="transparent" stroke-dasharray="0"></line>
                                        </g>
                                        <g id="SvgjsG3941" class="apexcharts-bar-series apexcharts-plot-series">
                                            <g id="SvgjsG3942" class="apexcharts-series" rel="1"
                                                seriesName="Inflation" data:realIndex="0">
                                                <path id="SvgjsPath3969"
                                                    d="M 321.01666666666665 312.348L 321.01666666666665 218.6436L 364.65 218.6436L 364.65 218.6436L 364.65 312.348L 364.65 312.348z"
                                                    fill="rgba(42,118,244,0.85)" fill-opacity="1" stroke-opacity="1"
                                                    stroke-linecap="square" stroke-width="0" stroke-dasharray="0"
                                                    class="apexcharts-bar-area" index="0"
                                                    clip-path="url(#gridRectMask51wc6w3tg)"
                                                    pathTo="M 321.01666666666665 312.348L 321.01666666666665 218.6436L 364.65 218.6436L 364.65 218.6436L 364.65 312.348L 364.65 312.348z"
                                                    pathFrom="M 321.01666666666665 312.348L 321.01666666666665 312.348L 364.65 312.348L 364.65 312.348L 364.65 312.348L 321.01666666666665 312.348"
                                                    cy="218.6436" cx="383.34999999999997" j="5" val="3.6"
                                                    barHeight="93.7044" barWidth="43.63333333333334"></path>
                                                <path id="SvgjsPath3974"
                                                    d="M 383.34999999999997 312.348L 383.34999999999997 229.0552L 426.9833333333333 229.0552L 426.9833333333333 229.0552L 426.9833333333333 312.348L 426.9833333333333 312.348z"
                                                    fill="rgba(42,118,244,0.85)" fill-opacity="1" stroke-opacity="1"
                                                    stroke-linecap="square" stroke-width="0" stroke-dasharray="0"
                                                    class="apexcharts-bar-area" index="0"
                                                    clip-path="url(#gridRectMask51wc6w3tg)"
                                                    pathTo="M 383.34999999999997 312.348L 383.34999999999997 229.0552L 426.9833333333333 229.0552L 426.9833333333333 229.0552L 426.9833333333333 312.348L 426.9833333333333 312.348z"
                                                    pathFrom="M 383.34999999999997 312.348L 383.34999999999997 312.348L 426.9833333333333 312.348L 426.9833333333333 312.348L 426.9833333333333 312.348L 383.34999999999997 312.348"
                                                    cy="229.0552" cx="445.6833333333333" j="6" val="3.2"
                                                    barHeight="83.2928" barWidth="43.63333333333334"></path>
                                                <path id="SvgjsPath3979"
                                                    d="M 445.6833333333333 312.348L 445.6833333333333 252.48130000000003L 489.3166666666666 252.48130000000003L 489.3166666666666 252.48130000000003L 489.3166666666666 312.348L 489.3166666666666 312.348z"
                                                    fill="rgba(42,118,244,0.85)" fill-opacity="1" stroke-opacity="1"
                                                    stroke-linecap="square" stroke-width="0" stroke-dasharray="0"
                                                    class="apexcharts-bar-area" index="0"
                                                    clip-path="url(#gridRectMask51wc6w3tg)"
                                                    pathTo="M 445.6833333333333 312.348L 445.6833333333333 252.48130000000003L 489.3166666666666 252.48130000000003L 489.3166666666666 252.48130000000003L 489.3166666666666 312.348L 489.3166666666666 312.348z"
                                                    pathFrom="M 445.6833333333333 312.348L 445.6833333333333 312.348L 489.3166666666666 312.348L 489.3166666666666 312.348L 489.3166666666666 312.348L 445.6833333333333 312.348"
                                                    cy="252.48130000000003" cx="508.0166666666666" j="7" val="2.3"
                                                    barHeight="59.866699999999994" barWidth="43.63333333333334"></path>
                                                <path id="SvgjsPath3984"
                                                    d="M 508.0166666666666 312.348L 508.0166666666666 275.9074L 551.65 275.9074L 551.65 275.9074L 551.65 312.348L 551.65 312.348z"
                                                    fill="rgba(42,118,244,0.85)" fill-opacity="1" stroke-opacity="1"
                                                    stroke-linecap="square" stroke-width="0" stroke-dasharray="0"
                                                    class="apexcharts-bar-area" index="0"
                                                    clip-path="url(#gridRectMask51wc6w3tg)"
                                                    pathTo="M 508.0166666666666 312.348L 508.0166666666666 275.9074L 551.65 275.9074L 551.65 275.9074L 551.65 312.348L 551.65 312.348z"
                                                    pathFrom="M 508.0166666666666 312.348L 508.0166666666666 312.348L 551.65 312.348L 551.65 312.348L 551.65 312.348L 508.0166666666666 312.348"
                                                    cy="275.9074" cx="570.3499999999999" j="8" val="1.4"
                                                    barHeight="36.440599999999996" barWidth="43.63333333333334"></path>
                                                <path id="SvgjsPath3989"
                                                    d="M 570.3499999999999 312.348L 570.3499999999999 291.5248L 613.9833333333332 291.5248L 613.9833333333332 291.5248L 613.9833333333332 312.348L 613.9833333333332 312.348z"
                                                    fill="rgba(42,118,244,0.85)" fill-opacity="1" stroke-opacity="1"
                                                    stroke-linecap="square" stroke-width="0" stroke-dasharray="0"
                                                    class="apexcharts-bar-area" index="0"
                                                    clip-path="url(#gridRectMask51wc6w3tg)"
                                                    pathTo="M 570.3499999999999 312.348L 570.3499999999999 291.5248L 613.9833333333332 291.5248L 613.9833333333332 291.5248L 613.9833333333332 312.348L 613.9833333333332 312.348z"
                                                    pathFrom="M 570.3499999999999 312.348L 570.3499999999999 312.348L 613.9833333333332 312.348L 613.9833333333332 312.348L 613.9833333333332 312.348L 570.3499999999999 312.348"
                                                    cy="291.5248" cx="632.6833333333333" j="9" val="0.8"
                                                    barHeight="20.8232" barWidth="43.63333333333334"></path>
                                                <path id="SvgjsPath3994"
                                                    d="M 632.6833333333333 312.348L 632.6833333333333 299.3335L 676.3166666666666 299.3335L 676.3166666666666 299.3335L 676.3166666666666 312.348L 676.3166666666666 312.348z"
                                                    fill="rgba(42,118,244,0.85)" fill-opacity="1" stroke-opacity="1"
                                                    stroke-linecap="square" stroke-width="0" stroke-dasharray="0"
                                                    class="apexcharts-bar-area" index="0"
                                                    clip-path="url(#gridRectMask51wc6w3tg)"
                                                    pathTo="M 632.6833333333333 312.348L 632.6833333333333 299.3335L 676.3166666666666 299.3335L 676.3166666666666 299.3335L 676.3166666666666 312.348L 676.3166666666666 312.348z"
                                                    pathFrom="M 632.6833333333333 312.348L 632.6833333333333 312.348L 676.3166666666666 312.348L 676.3166666666666 312.348L 676.3166666666666 312.348L 632.6833333333333 312.348"
                                                    cy="299.3335" cx="695.0166666666667" j="10" val="0.5"
                                                    barHeight="13.0145" barWidth="43.63333333333334"></path>
                                                <path id="SvgjsPath3999"
                                                    d="M 695.0166666666667 312.348L 695.0166666666667 307.1422L 738.65 307.1422L 738.65 307.1422L 738.65 312.348L 738.65 312.348z"
                                                    fill="rgba(42,118,244,0.85)" fill-opacity="1" stroke-opacity="1"
                                                    stroke-linecap="square" stroke-width="0" stroke-dasharray="0"
                                                    class="apexcharts-bar-area" index="0"
                                                    clip-path="url(#gridRectMask51wc6w3tg)"
                                                    pathTo="M 695.0166666666667 312.348L 695.0166666666667 307.1422L 738.65 307.1422L 738.65 307.1422L 738.65 312.348L 738.65 312.348z"
                                                    pathFrom="M 695.0166666666667 312.348L 695.0166666666667 312.348L 738.65 312.348L 738.65 312.348L 738.65 312.348L 695.0166666666667 312.348"
                                                    cy="307.1422" cx="757.35" j="11" val="0.2"
                                                    barHeight="5.2058" barWidth="43.63333333333334"></path>
                                            </g>
                                            <g id="SvgjsG3943" class="apexcharts-datalabels" data:realIndex="0">
                                                <g id="SvgjsG3971" class="apexcharts-data-labels" transform="rotate(0)">
                                                    <text id="SvgjsText3973" font-family="Helvetica, Arial, sans-serif"
                                                        x="342.8333333333333" y="212.6436" text-anchor="middle"
                                                        dominant-baseline="auto" font-size="12px" font-weight="600"
                                                        fill="#304758" class="apexcharts-datalabel"
                                                        cx="342.8333333333333" cy="212.6436"
                                                        style="font-family: Helvetica, Arial, sans-serif;">3.6%</text>
                                                </g>
                                                <g id="SvgjsG3976" class="apexcharts-data-labels" transform="rotate(0)">
                                                    <text id="SvgjsText3978" font-family="Helvetica, Arial, sans-serif"
                                                        x="405.16666666666663" y="223.0552" text-anchor="middle"
                                                        dominant-baseline="auto" font-size="12px" font-weight="600"
                                                        fill="#304758" class="apexcharts-datalabel"
                                                        cx="405.16666666666663" cy="223.0552"
                                                        style="font-family: Helvetica, Arial, sans-serif;">3.2%</text>
                                                </g>
                                                <g id="SvgjsG3981" class="apexcharts-data-labels" transform="rotate(0)">
                                                    <text id="SvgjsText3983" font-family="Helvetica, Arial, sans-serif"
                                                        x="467.49999999999994" y="246.48130000000003" text-anchor="middle"
                                                        dominant-baseline="auto" font-size="12px" font-weight="600"
                                                        fill="#304758" class="apexcharts-datalabel"
                                                        cx="467.49999999999994" cy="246.48130000000003"
                                                        style="font-family: Helvetica, Arial, sans-serif;">2.3%</text>
                                                </g>
                                                <g id="SvgjsG3986" class="apexcharts-data-labels" transform="rotate(0)">
                                                    <text id="SvgjsText3988" font-family="Helvetica, Arial, sans-serif"
                                                        x="529.8333333333333" y="269.9074" text-anchor="middle"
                                                        dominant-baseline="auto" font-size="12px" font-weight="600"
                                                        fill="#304758" class="apexcharts-datalabel"
                                                        cx="529.8333333333333" cy="269.9074"
                                                        style="font-family: Helvetica, Arial, sans-serif;">1.4%</text>
                                                </g>
                                                <g id="SvgjsG3991" class="apexcharts-data-labels" transform="rotate(0)">
                                                    <text id="SvgjsText3993" font-family="Helvetica, Arial, sans-serif"
                                                        x="592.1666666666666" y="285.5248" text-anchor="middle"
                                                        dominant-baseline="auto" font-size="12px" font-weight="600"
                                                        fill="#304758" class="apexcharts-datalabel"
                                                        cx="592.1666666666666" cy="285.5248"
                                                        style="font-family: Helvetica, Arial, sans-serif;">0.8%</text>
                                                </g>
                                                <g id="SvgjsG3996" class="apexcharts-data-labels" transform="rotate(0)">
                                                    <text id="SvgjsText3998" font-family="Helvetica, Arial, sans-serif"
                                                        x="654.5" y="293.3335" text-anchor="middle"
                                                        dominant-baseline="auto" font-size="12px" font-weight="600"
                                                        fill="#304758" class="apexcharts-datalabel" cx="654.5"
                                                        cy="293.3335"
                                                        style="font-family: Helvetica, Arial, sans-serif;">0.5%</text>
                                                </g>
                                                <g id="SvgjsG4001" class="apexcharts-data-labels" transform="rotate(0)">
                                                    <text id="SvgjsText4003" font-family="Helvetica, Arial, sans-serif"
                                                        x="716.8333333333334" y="301.1422" text-anchor="middle"
                                                        dominant-baseline="auto" font-size="12px" font-weight="600"
                                                        fill="#304758" class="apexcharts-datalabel"
                                                        cx="716.8333333333334" cy="301.1422"
                                                        style="font-family: Helvetica, Arial, sans-serif;">0.2%</text>
                                                </g>
                                            </g>
                                        </g>
                                        <line id="SvgjsLine4072" x1="0" y1="0" x2="748"
                                            y2="0" stroke="#b6b6b6" stroke-dasharray="0" stroke-width="1"
                                            class="apexcharts-ycrosshairs"></line>
                                        <line id="SvgjsLine4073" x1="0" y1="0" x2="748"
                                            y2="0" stroke-dasharray="0" stroke-width="0"
                                            class="apexcharts-ycrosshairs-hidden"></line>
                                        <g id="SvgjsG4074" class="apexcharts-yaxis-annotations"></g>
                                        <g id="SvgjsG4075" class="apexcharts-xaxis-annotations"></g>
                                        <g id="SvgjsG4076" class="apexcharts-point-annotations"></g>
                                    </g><text id="SvgjsText3931" font-family="Helvetica, Arial, sans-serif" x="390"
                                        y="366.5" text-anchor="middle" dominant-baseline="auto" font-size="14px"
                                        font-weight="900" fill="#8997bd" class="apexcharts-title-text"
                                        style="font-family: Helvetica, Arial, sans-serif; opacity: 1;">المعاملات
                                        بلشهر</text>
                                    <g id="SvgjsG4043" class="apexcharts-yaxis" rel="0"
                                        transform="translate(-8, 0)">
                                        <g id="SvgjsG4044" class="apexcharts-yaxis-texts-g"></g>
                                    </g>
                                    <g id="SvgjsG3930" class="apexcharts-annotations"></g>
                                </svg>
                                <div class="apexcharts-legend"></div>
                                <div class="apexcharts-tooltip apexcharts-theme-light"
                                    style="left: 239.483px; top: 55.652px;">
                                    <div class="apexcharts-tooltip-title"
                                        style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">Apr</div>
                                    <div class="apexcharts-tooltip-series-group apexcharts-active" style="display: flex;">
                                        <span class="apexcharts-tooltip-marker"
                                            style="background-color: rgb(42, 118, 244);"></span>
                                        <div class="apexcharts-tooltip-text"
                                            style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                            <div class="apexcharts-tooltip-y-group"><span
                                                    class="apexcharts-tooltip-text-label">Inflation: </span><span
                                                    class="apexcharts-tooltip-text-value">10.1%</span></div>
                                            <div class="apexcharts-tooltip-z-group"><span
                                                    class="apexcharts-tooltip-text-z-label"></span><span
                                                    class="apexcharts-tooltip-text-z-value"></span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="apexcharts-xaxistooltip apexcharts-xaxistooltip-top apexcharts-theme-light"
                                    style="left: 217.937px; top: -28px;">
                                    <div class="apexcharts-xaxistooltip-text"
                                        style="font-family: Helvetica, Arial, sans-serif; font-size: 12px; min-width: 21.1009px;">
                                        Apr</div>
                                </div>
                            </div>
                        </div>
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 781px; height: 371px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div><!--end card-body-->
            </div><!--end card-->
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">الطلبات</h4>
                </div><!--end card-header-->
                <div class="card-body">
                    <div class="chart-demo m-0" style="position: relative;">
                        <div id="apex_radialbar2" class="apex-charts" style="min-height: 350.7px;">
                            <div id="apexchartshg8hz5j7"
                                class="apexcharts-canvas apexchartshg8hz5j7 apexcharts-theme-light"
                                style="width: 503px; height: 350.7px;"><svg id="SvgjsSvg10555" width="503"
                                    height="350.70000000000005" xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs"
                                    class="apexcharts-svg" xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                    style="background: transparent;">
                                    <g id="SvgjsG10557" class="apexcharts-inner apexcharts-graphical"
                                        transform="translate(89.5, 0)">
                                        <defs id="SvgjsDefs10556">
                                            <clipPath id="gridRectMaskhg8hz5j7">
                                                <rect id="SvgjsRect10559" width="332" height="350" x="-3" y="-1"
                                                    rx="0" ry="0" opacity="1" stroke-width="0"
                                                    stroke="none" stroke-dasharray="0" fill="#fff"></rect>
                                            </clipPath>
                                            <clipPath id="gridRectMarkerMaskhg8hz5j7">
                                                <rect id="SvgjsRect10560" width="330" height="352" x="-2" y="-2"
                                                    rx="0" ry="0" opacity="1" stroke-width="0"
                                                    stroke="none" stroke-dasharray="0" fill="#fff"></rect>
                                            </clipPath>
                                            <filter id="SvgjsFilter10916" filterUnits="userSpaceOnUse" width="200%"
                                                height="200%" x="-50%" y="-50%">
                                                <feComponentTransfer id="SvgjsFeComponentTransfer10917"
                                                    result="SvgjsFeComponentTransfer10917Out" in="SourceGraphic">
                                                    <feFuncR id="SvgjsFeFuncR10918" type="linear" slope="0.65">
                                                    </feFuncR>
                                                    <feFuncG id="SvgjsFeFuncG10919" type="linear" slope="0.65">
                                                    </feFuncG>
                                                    <feFuncB id="SvgjsFeFuncB10920" type="linear" slope="0.65">
                                                    </feFuncB>
                                                    <feFuncA id="SvgjsFeFuncA10921" type="identity"></feFuncA>
                                                </feComponentTransfer>
                                            </filter>
                                        </defs>
                                        <g id="SvgjsG10562" class="apexcharts-radialbar">
                                            <g id="SvgjsG10563">
                                                <g id="SvgjsG10564" class="apexcharts-tracks">
                                                    <g id="SvgjsG10565"
                                                        class="apexcharts-radialbar-track apexcharts-track"
                                                        rel="1">
                                                        <path id="apexcharts-radialbarTrack-0"
                                                            d="M 163 32.30731707317071 A 141.6926829268293 141.6926829268293 0 1 1 162.97526996169495 32.30731923127368"
                                                            fill="none" fill-opacity="1"
                                                            stroke="rgba(185,193,212,0.85)" stroke-opacity="0.5"
                                                            stroke-linecap="butt" stroke-width="11.034341463414638"
                                                            stroke-dasharray="0" class="apexcharts-radialbar-area"
                                                            data:pathOrig="M 163 32.30731707317071 A 141.6926829268293 141.6926829268293 0 1 1 162.97526996169495 32.30731923127368">
                                                        </path>
                                                    </g>
                                                    <g id="SvgjsG10567"
                                                        class="apexcharts-radialbar-track apexcharts-track"
                                                        rel="2">
                                                        <path id="apexcharts-radialbarTrack-1"
                                                            d="M 163 48.68292682926827 A 125.31707317073173 125.31707317073173 0 1 1 162.9781280447531 48.68292873795643"
                                                            fill="none" fill-opacity="1"
                                                            stroke="rgba(185,193,212,0.85)" stroke-opacity="0.5"
                                                            stroke-linecap="butt" stroke-width="11.034341463414638"
                                                            stroke-dasharray="0" class="apexcharts-radialbar-area"
                                                            data:pathOrig="M 163 48.68292682926827 A 125.31707317073173 125.31707317073173 0 1 1 162.9781280447531 48.68292873795643">
                                                        </path>
                                                    </g>
                                                    <g id="SvgjsG10569"
                                                        class="apexcharts-radialbar-track apexcharts-track"
                                                        rel="3">
                                                        <path id="apexcharts-radialbarTrack-2"
                                                            d="M 163 65.05853658536583 A 108.94146341463417 108.94146341463417 0 1 1 162.98098612781126 65.0585382446392"
                                                            fill="none" fill-opacity="1"
                                                            stroke="rgba(185,193,212,0.85)" stroke-opacity="0.5"
                                                            stroke-linecap="butt" stroke-width="11.034341463414638"
                                                            stroke-dasharray="0" class="apexcharts-radialbar-area"
                                                            data:pathOrig="M 163 65.05853658536583 A 108.94146341463417 108.94146341463417 0 1 1 162.98098612781126 65.0585382446392">
                                                        </path>
                                                    </g>
                                                    <g id="SvgjsG10571"
                                                        class="apexcharts-radialbar-track apexcharts-track"
                                                        rel="4">
                                                        <path id="apexcharts-radialbarTrack-3"
                                                            d="M 163 81.43414634146339 A 92.56585365853661 92.56585365853661 0 1 1 162.9838442108694 81.43414775132196"
                                                            fill="none" fill-opacity="1"
                                                            stroke="rgba(185,193,212,0.85)" stroke-opacity="0.5"
                                                            stroke-linecap="butt" stroke-width="11.034341463414638"
                                                            stroke-dasharray="0" class="apexcharts-radialbar-area"
                                                            data:pathOrig="M 163 81.43414634146339 A 92.56585365853661 92.56585365853661 0 1 1 162.9838442108694 81.43414775132196">
                                                        </path>
                                                    </g>
                                                </g>
                                                <g id="SvgjsG10573">
                                                    <g id="SvgjsG10578" class="apexcharts-series apexcharts-radial-series"
                                                        seriesName="Apples" rel="1" data:realIndex="0">
                                                        <path id="SvgjsPath10579"
                                                            d="M 163 32.30731707317071 A 141.6926829268293 141.6926829268293 0 0 1 216.07901326318046 305.37516792000724"
                                                            fill="none" fill-opacity="0.85"
                                                            stroke="rgba(0,143,251,0.85)" stroke-opacity="1"
                                                            stroke-linecap="butt" stroke-width="11.375609756097564"
                                                            stroke-dasharray="0"
                                                            class="apexcharts-radialbar-area apexcharts-radialbar-slice-0"
                                                            data:angle="158" data:value="44" index="0" j="0"
                                                            data:pathOrig="M 163 32.30731707317071 A 141.6926829268293 141.6926829268293 0 0 1 216.07901326318046 305.37516792000724">
                                                        </path>
                                                    </g>
                                                    <g id="SvgjsG10580" class="apexcharts-series apexcharts-radial-series"
                                                        seriesName="Oranges" rel="2" data:realIndex="1">
                                                        <path id="SvgjsPath10581"
                                                            d="M 163 48.68292682926827 A 125.31707317073173 125.31707317073173 0 1 1 124.27489470491513 293.18361904206097"
                                                            fill="none" fill-opacity="0.85"
                                                            stroke="rgba(0,227,150,0.85)" stroke-opacity="1"
                                                            stroke-linecap="butt" stroke-width="11.375609756097564"
                                                            stroke-dasharray="0"
                                                            class="apexcharts-radialbar-area apexcharts-radialbar-slice-1"
                                                            data:angle="198" data:value="55" index="0" j="1"
                                                            data:pathOrig="M 163 48.68292682926827 A 125.31707317073173 125.31707317073173 0 1 1 124.27489470491513 293.18361904206097">
                                                        </path>
                                                    </g>
                                                    <g id="SvgjsG10582" class="apexcharts-series apexcharts-radial-series"
                                                        seriesName="Bananas" rel="3" data:realIndex="2">
                                                        <path id="SvgjsPath10583"
                                                            d="M 163 65.05853658536583 A 108.94146341463417 108.94146341463417 0 1 1 67.71764917295546 226.81586950712904"
                                                            fill="none" fill-opacity="0.85"
                                                            stroke="rgba(254,176,25,0.85)" stroke-opacity="1"
                                                            stroke-linecap="butt" stroke-width="11.375609756097564"
                                                            stroke-dasharray="0"
                                                            class="apexcharts-radialbar-area apexcharts-radialbar-slice-2"
                                                            data:angle="241" data:value="67" index="0" j="2"
                                                            data:pathOrig="M 163 65.05853658536583 A 108.94146341463417 108.94146341463417 0 1 1 67.71764917295546 226.81586950712904"
                                                            selected="true" filter="url(#SvgjsFilter10916)"></path>
                                                    </g>
                                                    <g id="SvgjsG10584" class="apexcharts-series apexcharts-radial-series"
                                                        seriesName="Berries" rel="4" data:realIndex="3">
                                                        <path id="SvgjsPath10585"
                                                            d="M 163 81.43414634146339 A 92.56585365853661 92.56585365853661 0 1 1 82.04008018206254 129.12318364002687"
                                                            fill="none" fill-opacity="0.85"
                                                            stroke="rgba(255,69,96,0.85)" stroke-opacity="1"
                                                            stroke-linecap="butt" stroke-width="11.375609756097564"
                                                            stroke-dasharray="0"
                                                            class="apexcharts-radialbar-area apexcharts-radialbar-slice-3"
                                                            data:angle="299" data:value="83" index="0" j="3"
                                                            data:pathOrig="M 163 81.43414634146339 A 92.56585365853661 92.56585365853661 0 1 1 82.04008018206254 129.12318364002687">
                                                        </path>
                                                    </g>
                                                    <circle id="SvgjsCircle10574" r="82.04868292682927" cx="163"
                                                        cy="174" class="apexcharts-radialbar-hollow"
                                                        fill="transparent"></circle>
                                                    <g id="SvgjsG10575" class="apexcharts-datalabels-group"
                                                        transform="translate(0, 0) scale(1)" style="opacity: 1;"><text
                                                            id="SvgjsText10576" font-family="Helvetica, Arial, sans-serif"
                                                            x="163" y="174" text-anchor="middle" dominant-baseline="auto"
                                                            font-size="16px" font-weight="600" fill="#8997bd"
                                                            class="apexcharts-text apexcharts-datalabel-label"
                                                            style="font-family: Helvetica, Arial, sans-serif; fill: rgb(137, 151, 189);">الاجمالي</text><text
                                                            id="SvgjsText10577" font-family="Helvetica, Arial, sans-serif"
                                                            x="163" y="206" text-anchor="middle" dominant-baseline="auto"
                                                            font-size="16px" font-weight="400" fill="#8997bd"
                                                            class="apexcharts-text apexcharts-datalabel-value"
                                                            style="font-family: Helvetica, Arial, sans-serif;">249</text>
                                                    </g>
                                                </g>
                                            </g>
                                        </g>
                                        <line id="SvgjsLine10586" x1="0" y1="0" x2="326"
                                            y2="0" stroke="#b6b6b6" stroke-dasharray="0" stroke-width="1"
                                            class="apexcharts-ycrosshairs"></line>
                                        <line id="SvgjsLine10587" x1="0" y1="0" x2="326"
                                            y2="0" stroke-dasharray="0" stroke-width="0"
                                            class="apexcharts-ycrosshairs-hidden"></line>
                                    </g>
                                    <g id="SvgjsG10558" class="apexcharts-annotations"></g>
                                </svg>
                                <div class="apexcharts-legend"></div>
                            </div>
                        </div>
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 504px; height: 371px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div><!--end card-body-->
            </div><!--end card-->
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">الفواتير</h4>
                </div><!--end card-header-->
                <div class="card-body">
                    <div class="" style="position: relative;">
                        <div id="apex_pie1" class="apex-charts" style="min-height: 287.7px;">
                            <div id="apexchartsvexzptqik"
                                class="apexcharts-canvas apexchartsvexzptqik apexcharts-theme-light"
                                style="width: 503px; height: 287.7px;"><svg id="SvgjsSvg10370" width="503"
                                    height="287.70000000000005" xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs"
                                    class="apexcharts-svg" xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                    style="background: transparent;">
                                    <foreignObject x="0" y="0" width="503" height="287.70000000000005">
                                        <div class="apexcharts-legend apexcharts-align-center position-bottom"
                                            xmlns="http://www.w3.org/1999/xhtml"
                                            style="inset: auto 0px -1px 20px; position: absolute;">
                                            <div class="apexcharts-legend-series" rel="1"
                                                data:collapsed="false" style="margin: 0px 5px;"><span
                                                    class="apexcharts-legend-marker" rel="1"
                                                    data:collapsed="false"
                                                    style="background: rgb(217, 230, 253); color: rgb(217, 230, 253); height: 12px; width: 12px; left: 0px; top: 0px; border-width: 0px; border-color: rgb(255, 255, 255); border-radius: 12px;"></span><span
                                                    class="apexcharts-legend-text" rel="1" i="0"
                                                    data:default-text="Series%201" data:collapsed="false"
                                                    style="color: rgb(55, 61, 63); font-size: 14px; font-weight: 400; font-family: Helvetica, Arial, sans-serif;">Series
                                                    1</span></div>
                                            <div class="apexcharts-legend-series" rel="2"
                                                data:collapsed="false" style="margin: 0px 5px;"><span
                                                    class="apexcharts-legend-marker" rel="2"
                                                    data:collapsed="false"
                                                    style="background: rgb(74, 138, 246); color: rgb(74, 138, 246); height: 12px; width: 12px; left: 0px; top: 0px; border-width: 0px; border-color: rgb(255, 255, 255); border-radius: 12px;"></span><span
                                                    class="apexcharts-legend-text" rel="2" i="1"
                                                    data:default-text="Series%202" data:collapsed="false"
                                                    style="color: rgb(55, 61, 63); font-size: 14px; font-weight: 400; font-family: Helvetica, Arial, sans-serif;">Series
                                                    2</span></div>
                                            <div class="apexcharts-legend-series" rel="3"
                                                data:collapsed="false" style="margin: 0px 5px;"><span
                                                    class="apexcharts-legend-marker" rel="3"
                                                    data:collapsed="false"
                                                    style="background: rgb(251, 198, 89); color: rgb(251, 198, 89); height: 12px; width: 12px; left: 0px; top: 0px; border-width: 0px; border-color: rgb(255, 255, 255); border-radius: 12px;"></span><span
                                                    class="apexcharts-legend-text" rel="3" i="2"
                                                    data:default-text="Series%203" data:collapsed="false"
                                                    style="color: rgb(55, 61, 63); font-size: 14px; font-weight: 400; font-family: Helvetica, Arial, sans-serif;">Series
                                                    3</span></div>
                                        </div>
                                        <style type="text/css">
                                            .apexcharts-legend {
                                                display: flex;
                                                overflow: auto;
                                                padding: 0 10px;
                                            }

                                            .apexcharts-legend.position-bottom,
                                            .apexcharts-legend.position-top {
                                                flex-wrap: wrap
                                            }

                                            .apexcharts-legend.position-right,
                                            .apexcharts-legend.position-left {
                                                flex-direction: column;
                                                bottom: 0;
                                            }

                                            .apexcharts-legend.position-bottom.apexcharts-align-left,
                                            .apexcharts-legend.position-top.apexcharts-align-left,
                                            .apexcharts-legend.position-right,
                                            .apexcharts-legend.position-left {
                                                justify-content: flex-start;
                                            }

                                            .apexcharts-legend.position-bottom.apexcharts-align-center,
                                            .apexcharts-legend.position-top.apexcharts-align-center {
                                                justify-content: center;
                                            }

                                            .apexcharts-legend.position-bottom.apexcharts-align-right,
                                            .apexcharts-legend.position-top.apexcharts-align-right {
                                                justify-content: flex-end;
                                            }

                                            .apexcharts-legend-series {
                                                cursor: pointer;
                                                line-height: normal;
                                            }

                                            .apexcharts-legend.position-bottom .apexcharts-legend-series,
                                            .apexcharts-legend.position-top .apexcharts-legend-series {
                                                display: flex;
                                                align-items: center;
                                            }

                                            .apexcharts-legend-text {
                                                position: relative;
                                                font-size: 14px;
                                            }

                                            .apexcharts-legend-text *,
                                            .apexcharts-legend-marker * {
                                                pointer-events: none;
                                            }

                                            .apexcharts-legend-marker {
                                                position: relative;
                                                display: inline-block;
                                                cursor: pointer;
                                                margin-right: 3px;
                                                border-style: solid;
                                            }

                                            .apexcharts-legend.apexcharts-align-right .apexcharts-legend-series,
                                            .apexcharts-legend.apexcharts-align-left .apexcharts-legend-series {
                                                display: inline-block;
                                            }

                                            .apexcharts-legend-series.apexcharts-no-click {
                                                cursor: auto;
                                            }

                                            .apexcharts-legend .apexcharts-hidden-zero-series,
                                            .apexcharts-legend .apexcharts-hidden-null-series {
                                                display: none !important;
                                            }

                                            .apexcharts-inactive-legend {
                                                opacity: 0.45;
                                            }
                                        </style>
                                    </foreignObject>
                                    <g id="SvgjsG10372" class="apexcharts-inner apexcharts-graphical"
                                        transform="translate(131.5, 0)">
                                        <defs id="SvgjsDefs10371">
                                            <clipPath id="gridRectMaskvexzptqik">
                                                <rect id="SvgjsRect10374" width="248" height="256" x="-3" y="-1"
                                                    rx="0" ry="0" opacity="1" stroke-width="0"
                                                    stroke="none" stroke-dasharray="0" fill="#fff"></rect>
                                            </clipPath>
                                            <clipPath id="gridRectMarkerMaskvexzptqik">
                                                <rect id="SvgjsRect10375" width="246" height="258" x="-2" y="-2"
                                                    rx="0" ry="0" opacity="1" stroke-width="0"
                                                    stroke="none" stroke-dasharray="0" fill="#fff"></rect>
                                            </clipPath>
                                            <filter id="SvgjsFilter10383" filterUnits="userSpaceOnUse" width="200%"
                                                height="200%" x="-50%" y="-50%">
                                                <feFlood id="SvgjsFeFlood10384" flood-color="#000000"
                                                    flood-opacity="0.45" result="SvgjsFeFlood10384Out"
                                                    in="SourceGraphic"></feFlood>
                                                <feComposite id="SvgjsFeComposite10385" in="SvgjsFeFlood10384Out"
                                                    in2="SourceAlpha" operator="in"
                                                    result="SvgjsFeComposite10385Out"></feComposite>
                                                <feOffset id="SvgjsFeOffset10386" dx="1" dy="1"
                                                    result="SvgjsFeOffset10386Out" in="SvgjsFeComposite10385Out">
                                                </feOffset>
                                                <feGaussianBlur id="SvgjsFeGaussianBlur10387" stdDeviation="1 "
                                                    result="SvgjsFeGaussianBlur10387Out" in="SvgjsFeOffset10386Out">
                                                </feGaussianBlur>
                                                <feMerge id="SvgjsFeMerge10388" result="SvgjsFeMerge10388Out"
                                                    in="SourceGraphic">
                                                    <feMergeNode id="SvgjsFeMergeNode10389"
                                                        in="SvgjsFeGaussianBlur10387Out"></feMergeNode>
                                                    <feMergeNode id="SvgjsFeMergeNode10390" in="[object Arguments]">
                                                    </feMergeNode>
                                                </feMerge>
                                                <feBlend id="SvgjsFeBlend10391" in="SourceGraphic"
                                                    in2="SvgjsFeMerge10388Out" mode="normal"
                                                    result="SvgjsFeBlend10391Out"></feBlend>
                                            </filter>
                                            <filter id="SvgjsFilter10395" filterUnits="userSpaceOnUse" width="200%"
                                                height="200%" x="-50%" y="-50%">
                                                <feFlood id="SvgjsFeFlood10396" flood-color="#000000"
                                                    flood-opacity="0.45" result="SvgjsFeFlood10396Out"
                                                    in="SourceGraphic"></feFlood>
                                                <feComposite id="SvgjsFeComposite10397" in="SvgjsFeFlood10396Out"
                                                    in2="SourceAlpha" operator="in"
                                                    result="SvgjsFeComposite10397Out"></feComposite>
                                                <feOffset id="SvgjsFeOffset10398" dx="1" dy="1"
                                                    result="SvgjsFeOffset10398Out" in="SvgjsFeComposite10397Out">
                                                </feOffset>
                                                <feGaussianBlur id="SvgjsFeGaussianBlur10399" stdDeviation="1 "
                                                    result="SvgjsFeGaussianBlur10399Out" in="SvgjsFeOffset10398Out">
                                                </feGaussianBlur>
                                                <feMerge id="SvgjsFeMerge10400" result="SvgjsFeMerge10400Out"
                                                    in="SourceGraphic">
                                                    <feMergeNode id="SvgjsFeMergeNode10401"
                                                        in="SvgjsFeGaussianBlur10399Out"></feMergeNode>
                                                    <feMergeNode id="SvgjsFeMergeNode10402" in="[object Arguments]">
                                                    </feMergeNode>
                                                </feMerge>
                                                <feBlend id="SvgjsFeBlend10403" in="SourceGraphic"
                                                    in2="SvgjsFeMerge10400Out" mode="normal"
                                                    result="SvgjsFeBlend10403Out"></feBlend>
                                            </filter>
                                            <filter id="SvgjsFilter10407" filterUnits="userSpaceOnUse" width="200%"
                                                height="200%" x="-50%" y="-50%">
                                                <feFlood id="SvgjsFeFlood10408" flood-color="#000000"
                                                    flood-opacity="0.45" result="SvgjsFeFlood10408Out"
                                                    in="SourceGraphic"></feFlood>
                                                <feComposite id="SvgjsFeComposite10409" in="SvgjsFeFlood10408Out"
                                                    in2="SourceAlpha" operator="in"
                                                    result="SvgjsFeComposite10409Out"></feComposite>
                                                <feOffset id="SvgjsFeOffset10410" dx="1" dy="1"
                                                    result="SvgjsFeOffset10410Out" in="SvgjsFeComposite10409Out">
                                                </feOffset>
                                                <feGaussianBlur id="SvgjsFeGaussianBlur10411" stdDeviation="1 "
                                                    result="SvgjsFeGaussianBlur10411Out" in="SvgjsFeOffset10410Out">
                                                </feGaussianBlur>
                                                <feMerge id="SvgjsFeMerge10412" result="SvgjsFeMerge10412Out"
                                                    in="SourceGraphic">
                                                    <feMergeNode id="SvgjsFeMergeNode10413"
                                                        in="SvgjsFeGaussianBlur10411Out"></feMergeNode>
                                                    <feMergeNode id="SvgjsFeMergeNode10414" in="[object Arguments]">
                                                    </feMergeNode>
                                                </feMerge>
                                                <feBlend id="SvgjsFeBlend10415" in="SourceGraphic"
                                                    in2="SvgjsFeMerge10412Out" mode="normal"
                                                    result="SvgjsFeBlend10415Out"></feBlend>
                                            </filter>
                                        </defs>
                                        <g id="SvgjsG10377" class="apexcharts-pie">
                                            <g id="SvgjsG10378" transform="translate(0, 0) scale(1)">
                                                <g id="SvgjsG10379" class="apexcharts-slices">
                                                    <g id="SvgjsG10380" class="apexcharts-series apexcharts-pie-series"
                                                        seriesName="Seriesx1" rel="1" data:realIndex="0">
                                                        <path id="SvgjsPath10381"
                                                            d="M 121.00000000000001 9.097560975609738 A 117.90243902439026 117.90243902439026 0 0 1 223.10650736326772 185.9512195121951 L 121 127 L 121.00000000000001 9.097560975609738"
                                                            fill="rgba(217,230,253,1)" fill-opacity="1"
                                                            stroke-opacity="1" stroke-linecap="butt" stroke-width="2"
                                                            stroke-dasharray="0"
                                                            class="apexcharts-pie-area apexcharts-pie-slice-0"
                                                            index="0" j="0" data:angle="120" data:startAngle="0"
                                                            data:strokeWidth="2" data:value="50"
                                                            data:pathOrig="M 121.00000000000001 9.097560975609738 A 117.90243902439026 117.90243902439026 0 0 1 223.10650736326772 185.9512195121951 L 121 127 L 121.00000000000001 9.097560975609738"
                                                            stroke="transparent"></path>
                                                    </g>
                                                    <g id="SvgjsG10392" class="apexcharts-series apexcharts-pie-series"
                                                        seriesName="Seriesx2" rel="2" data:realIndex="1">
                                                        <path id="SvgjsPath10393"
                                                            d="M 223.10650736326772 185.9512195121951 A 117.90243902439026 117.90243902439026 0 0 1 18.89349263673226 185.9512195121951 L 121 127 L 223.10650736326772 185.9512195121951"
                                                            fill="rgba(74,138,246,1)" fill-opacity="1"
                                                            stroke-opacity="1" stroke-linecap="butt" stroke-width="2"
                                                            stroke-dasharray="0"
                                                            class="apexcharts-pie-area apexcharts-pie-slice-1"
                                                            index="0" j="1" data:angle="120"
                                                            data:startAngle="120" data:strokeWidth="2" data:value="50"
                                                            data:pathOrig="M 223.10650736326772 185.9512195121951 A 117.90243902439026 117.90243902439026 0 0 1 18.89349263673226 185.9512195121951 L 121 127 L 223.10650736326772 185.9512195121951"
                                                            stroke="transparent"></path>
                                                    </g>
                                                    <g id="SvgjsG10404" class="apexcharts-series apexcharts-pie-series"
                                                        seriesName="Seriesx3" rel="3" data:realIndex="2">
                                                        <path id="SvgjsPath10405"
                                                            d="M 18.89349263673226 185.9512195121951 A 117.90243902439026 117.90243902439026 0 0 1 120.97942214253338 9.097562771366569 L 121 127 L 18.89349263673226 185.9512195121951"
                                                            fill="rgba(251,198,89,1)" fill-opacity="1"
                                                            stroke-opacity="1" stroke-linecap="butt" stroke-width="2"
                                                            stroke-dasharray="0"
                                                            class="apexcharts-pie-area apexcharts-pie-slice-2"
                                                            index="0" j="2" data:angle="120"
                                                            data:startAngle="240" data:strokeWidth="2" data:value="50"
                                                            data:pathOrig="M 18.89349263673226 185.9512195121951 A 117.90243902439026 117.90243902439026 0 0 1 120.97942214253338 9.097562771366569 L 121 127 L 18.89349263673226 185.9512195121951"
                                                            stroke="transparent"></path>
                                                    </g><text id="SvgjsText10382"
                                                        font-family="Helvetica, Arial, sans-serif" x="202.6852058906142"
                                                        y="79.8390243902439" text-anchor="middle"
                                                        dominant-baseline="auto" font-size="12px" font-weight="600"
                                                        fill="#ffffff" class="apexcharts-text apexcharts-pie-label"
                                                        filter="url(#SvgjsFilter10383)"
                                                        style="font-family: Helvetica, Arial, sans-serif;">33.3%</text><text
                                                        id="SvgjsText10394" font-family="Helvetica, Arial, sans-serif"
                                                        x="121" y="221.32195121951221" text-anchor="middle"
                                                        dominant-baseline="auto" font-size="12px" font-weight="600"
                                                        fill="#ffffff" class="apexcharts-text apexcharts-pie-label"
                                                        filter="url(#SvgjsFilter10395)"
                                                        style="font-family: Helvetica, Arial, sans-serif;">33.3%</text><text
                                                        id="SvgjsText10406" font-family="Helvetica, Arial, sans-serif"
                                                        x="39.314794109385815" y="79.83902439024388" text-anchor="middle"
                                                        dominant-baseline="auto" font-size="12px" font-weight="600"
                                                        fill="#ffffff" class="apexcharts-text apexcharts-pie-label"
                                                        filter="url(#SvgjsFilter10407)"
                                                        style="font-family: Helvetica, Arial, sans-serif;">33.3%</text>
                                                </g>
                                            </g>
                                        </g>
                                        <line id="SvgjsLine10416" x1="0" y1="0" x2="242"
                                            y2="0" stroke="#b6b6b6" stroke-dasharray="0" stroke-width="1"
                                            class="apexcharts-ycrosshairs"></line>
                                        <line id="SvgjsLine10417" x1="0" y1="0" x2="242"
                                            y2="0" stroke-dasharray="0" stroke-width="0"
                                            class="apexcharts-ycrosshairs-hidden"></line>
                                    </g>
                                    <g id="SvgjsG10373" class="apexcharts-annotations"></g>
                                </svg>
                                <div class="apexcharts-tooltip apexcharts-theme-dark"
                                    style="left: 306.867px; top: 80px;">
                                    <div class="apexcharts-tooltip-series-group apexcharts-active"
                                        style="display: flex; background-color: rgb(217, 230, 253);"><span
                                            class="apexcharts-tooltip-marker"
                                            style="background-color: rgb(217, 230, 253); display: none;"></span>
                                        <div class="apexcharts-tooltip-text"
                                            style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                            <div class="apexcharts-tooltip-y-group"><span
                                                    class="apexcharts-tooltip-text-label">Series 1: </span><span
                                                    class="apexcharts-tooltip-text-value">50</span></div>
                                            <div class="apexcharts-tooltip-z-group"><span
                                                    class="apexcharts-tooltip-text-z-label"></span><span
                                                    class="apexcharts-tooltip-text-z-value"></span></div>
                                        </div>
                                    </div>
                                    <div class="apexcharts-tooltip-series-group"
                                        style="display: none; background-color: rgb(217, 230, 253);"><span
                                            class="apexcharts-tooltip-marker"
                                            style="background-color: rgb(217, 230, 253); display: none;"></span>
                                        <div class="apexcharts-tooltip-text"
                                            style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                            <div class="apexcharts-tooltip-y-group"><span
                                                    class="apexcharts-tooltip-text-label">Series 1: </span><span
                                                    class="apexcharts-tooltip-text-value">50</span></div>
                                            <div class="apexcharts-tooltip-z-group"><span
                                                    class="apexcharts-tooltip-text-z-label"></span><span
                                                    class="apexcharts-tooltip-text-z-value"></span></div>
                                        </div>
                                    </div>
                                    <div class="apexcharts-tooltip-series-group"
                                        style="display: none; background-color: rgb(217, 230, 253);"><span
                                            class="apexcharts-tooltip-marker"
                                            style="background-color: rgb(217, 230, 253); display: none;"></span>
                                        <div class="apexcharts-tooltip-text"
                                            style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                            <div class="apexcharts-tooltip-y-group"><span
                                                    class="apexcharts-tooltip-text-label">Series 1: </span><span
                                                    class="apexcharts-tooltip-text-value">50</span></div>
                                            <div class="apexcharts-tooltip-z-group"><span
                                                    class="apexcharts-tooltip-text-z-label"></span><span
                                                    class="apexcharts-tooltip-text-z-value"></span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 504px; height: 289px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div><!--end card-body-->
            </div><!--end card-->
        </div>
    </div>
    <div class="card shadow rounded p-4">
        <h4 class="mb-3">المبيعات خلال الأشهر</h4>
        <canvas id="salesChart" height="100"></canvas>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر',
                    'نوفمبر', 'ديسمبر'
                ],
                datasets: [{
                    label: 'إجمالي المبيعات',
                    data: {!! json_encode(array_replace(array_fill(1, 12, 0), $salesByMonth)) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]

            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endpush
