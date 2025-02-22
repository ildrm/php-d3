<?php

class D3ChartBuilder {
    private $width = 600;
    private $height = 400;
    private $data = [];
    private $chartType = '';
    private $options = [];

    public function __construct($chartType, $data, $options = []) {
        $this->chartType = strtolower($chartType);
        $this->data = $data;
        $this->options = array_merge([
            'width' => $this->width,
            'height' => $this->height,
            'margin' => ['top' => 20, 'right' => 20, 'bottom' => 30, 'left' => 40],
            'colors' => ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b'],
            'title' => 'نمودار ' . ucfirst($chartType),
            'animation' => false,
            'interactive' => false,
        ], $options);
        $this->width = $this->options['width'];
        $this->height = $this->options['height'];
    }

    public function render() {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<script src="https://d3js.org/d3.v7.min.js"></script>';
        if ($this->chartType === 'sankey') {
            $html .= '<script src="https://unpkg.com/d3-sankey@0.12.3/dist/d3-sankey.min.js"></script>';
        }
        $html .= '<style>' . $this->generateCSS() . '</style>';
        $html .= '</head><body>';
        $html .= '<div id="chart"></div>';
        $html .= '<script>' . $this->generateJavaScript() . '</script>';
        $html .= '</body></html>';
        return $html;
    }

    private function generateCSS() {
        return "
            #chart {
                width: {$this->width}px;
                height: {$this->height}px;
                margin: auto;
                font-family: Arial, sans-serif;
            }
            .title {
                font-size: 20px;
                text-align: center;
            }
            .bar:hover, .dot:hover, .arc:hover, .node:hover, .link:hover {
                opacity: 0.7;
            }
            .node rect {
                fill-opacity: 0.9;
                stroke: #fff;
                stroke-width: 1px;
            }
            .link {
                fill: none;
                stroke-opacity: 0.5;
            }
            .tooltip {
                position: absolute;
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 5px;
                border-radius: 3px;
                pointer-events: none;
            }
        ";
    }

    private function generateJavaScript() {
        $dataJson = json_encode($this->data);
        $margin = isset($this->options['margin']) && is_array($this->options['margin']) ? $this->options['margin'] : [];
        $marginTop = isset($margin['top']) ? $margin['top'] : 20;
        $marginRight = isset($margin['right']) ? $margin['right'] : 20;
        $marginBottom = isset($margin['bottom']) ? $margin['bottom'] : 30;
        $marginLeft = isset($margin['left']) ? $margin['left'] : 40;

        $width = $this->width - $marginLeft - $marginRight;
        $height = $this->height - $marginTop - $marginBottom;
        $chartCode = '';

        $baseCode = "
            const data = $dataJson;
            const margin = {top: $marginTop, right: $marginRight, bottom: $marginBottom, left: $marginLeft};
            const width = $width;
            const height = $height;
            const fullWidth = $this->width;
            const fullHeight = $this->height;

            const svg = d3.select('#chart')
                .append('svg')
                .attr('width', fullWidth)
                .attr('height', fullHeight)
                .append('g')
                .attr('transform', `translate(${marginLeft},${marginTop})`);

            svg.append('text')
                .attr('class', 'title')
                .attr('x', width / 2)
                .attr('y', -10)
                .attr('text-anchor', 'middle')
                .text('{$this->options['title']}');

            const tooltip = d3.select('body').append('div')
                .attr('class', 'tooltip')
                .style('opacity', 0);
        ";

        switch ($this->chartType) {
            case 'bar':
                $chartCode = $this->generateBarChart($width, $height);
                break;
            case 'line':
                $chartCode = $this->generateLineChart($width, $height);
                break;
            case 'scatter':
                $chartCode = $this->generateScatterPlot($width, $height);
                break;
            case 'pie':
                $chartCode = $this->generatePieChart($width, $height, $marginLeft, $marginTop);
                break;
            case 'area':
                $chartCode = $this->generateAreaChart($width, $height);
                break;
            case 'box':
                $chartCode = $this->generateBoxPlot($width, $height);
                break;
            case 'force':
                $chartCode = $this->generateForceGraph($width, $height);
                break;
            case 'sankey':
                $chartCode = $this->generateSankeyDiagram($width, $height);
                break;
            case 'treemap':
                $chartCode = $this->generateTreemap($width, $height);
                break;
            default:
                return "alert('نوع نمودار پشتیبانی نمی‌شود');";
        }

        return $baseCode . $chartCode;
    }

    private function generateBarChart($width, $height) {
        $colors = json_encode($this->options['colors']);
        $animationCode = $this->options['animation'] ? ".transition().duration(1000)" : "";
        $interactiveCode = $this->options['interactive'] ? "
            .on('mouseover', function(event, d) {
                tooltip.transition().duration(200).style('opacity', 0.9);
                tooltip.html(`Value: \${d.value}`).style('left', (event.pageX + 5) + 'px').style('top', (event.pageY - 28) + 'px');
            })
            .on('mouseout', function() {
                tooltip.transition().duration(500).style('opacity', 0);
            })
        " : "";
        return "
            const x = d3.scaleBand()
                .domain(data.map(d => d.label))
                .range([0, width])
                .padding(0.1);

            const y = d3.scaleLinear()
                .domain([0, d3.max(data, d => d.value)])
                .nice()
                .range([height, 0]);

            svg.append('g')
                .selectAll('.bar')
                .data(data)
                .enter().append('rect')
                .attr('class', 'bar')
                .attr('x', d => x(d.label))
                .attr('width', x.bandwidth())
                .attr('y', height)
                .attr('height', 0)
                .attr('fill', {$colors}[0])
                $interactiveCode
                $animationCode
                .attr('y', d => y(d.value))
                .attr('height', d => height - y(d.value));

            svg.append('g')
                .attr('transform', `translate(0,${height})`)
                .call(d3.axisBottom(x));

            svg.append('g')
                .call(d3.axisLeft(y));
        ";
    }

    private function generateLineChart($width, $height) {
        $colors = json_encode($this->options['colors']);
        return "
            const x = d3.scaleLinear()
                .domain(d3.extent(data, d => d.x))
                .range([0, width]);

            const y = d3.scaleLinear()
                .domain([0, d3.max(data, d => d.y)])
                .nice()
                .range([height, 0]);

            const line = d3.line()
                .x(d => x(d.x))
                .y(d => y(d.y));

            svg.append('path')
                .datum(data)
                .attr('fill', 'none')
                .attr('stroke', {$colors}[0])
                .attr('stroke-width', 2)
                .attr('d', line);

            svg.append('g')
                .attr('transform', `translate(0,${height})`)
                .call(d3.axisBottom(x));

            svg.append('g')
                .call(d3.axisLeft(y));
        ";
    }

    private function generateScatterPlot($width, $height) {
        $colors = json_encode($this->options['colors']);
        return "
            const x = d3.scaleLinear()
                .domain(d3.extent(data, d => d.x))
                .range([0, width]);

            const y = d3.scaleLinear()
                .domain(d3.extent(data, d => d.y))
                .nice()
                .range([height, 0]);

            svg.append('g')
                .selectAll('.dot')
                .data(data)
                .enter().append('circle')
                .attr('class', 'dot')
                .attr('cx', d => x(d.x))
                .attr('cy', d => y(d.y))
                .attr('r', 5)
                .attr('fill', {$colors}[0]);

            svg.append('g')
                .attr('transform', `translate(0,${height})`)
                .call(d3.axisBottom(x));

            svg.append('g')
                .call(d3.axisLeft(y));
        ";
    }

    private function generatePieChart($width, $height, $marginLeft, $marginTop) {
        $colors = json_encode($this->options['colors']);
        $centerX = $marginLeft + $width / 2;
        $centerY = $marginTop + $height / 2;
        return "
            const radius = Math.min($width, $height) / 2;
            const pie = d3.pie()
                .value(d => d.value);

            const arc = d3.arc()
                .innerRadius(0)
                .outerRadius(radius);

            const arcs = svg.selectAll('.arc')
                .data(pie(data))
                .enter().append('g')
                .attr('class', 'arc')
                .attr('transform', `translate($centerX, $centerY)`);

            arcs.append('path')
                .attr('d', arc)
                .attr('fill', (d, i) => {$colors}[i % {$colors}.length]);
        ";
    }

    private function generateAreaChart($width, $height) {
        $colors = json_encode($this->options['colors']);
        return "
            const x = d3.scaleLinear()
                .domain(d3.extent(data, d => d.x))
                .range([0, width]);

            const y = d3.scaleLinear()
                .domain([0, d3.max(data, d => d.y)])
                .nice()
                .range([height, 0]);

            const area = d3.area()
                .x(d => x(d.x))
                .y0(height)
                .y1(d => y(d.y));

            svg.append('path')
                .datum(data)
                .attr('fill', {$colors}[0])
                .attr('opacity', 0.7)
                .attr('d', area);

            svg.append('g')
                .attr('transform', `translate(0,${height})`)
                .call(d3.axisBottom(x));

            svg.append('g')
                .call(d3.axisLeft(y));
        ";
    }

    private function generateBoxPlot($width, $height) {
        return "
            const x = d3.scaleBand()
                .domain(data.map(d => d.group))
                .range([0, width])
                .padding(0.2);

            const y = d3.scaleLinear()
                .domain([d3.min(data, d => d.values.q1), d3.max(data, d => d.values.q3)])
                .nice()
                .range([height, 0]);

            const boxWidth = x.bandwidth();

            svg.selectAll('.box')
                .data(data)
                .enter().append('rect')
                .attr('x', d => x(d.group))
                .attr('y', d => y(d.values.q3))
                .attr('width', boxWidth)
                .attr('height', d => y(d.values.q1) - y(d.values.q3))
                .attr('fill', '#69b3a2');

            svg.append('g')
                .attr('transform', `translate(0,${height})`)
                .call(d3.axisBottom(x));

            svg.append('g')
                .call(d3.axisLeft(y));
        ";
    }

    private function generateForceGraph($width, $height) {
        $colors = json_encode($this->options['colors']);
        return "
            const simulation = d3.forceSimulation(data.nodes)
                .force('link', d3.forceLink(data.links).id(d => d.id).distance(50))
                .force('charge', d3.forceManyBody().strength(-100))
                .force('center', d3.forceCenter(width / 2, height / 2));

            const link = svg.append('g')
                .selectAll('line')
                .data(data.links)
                .enter().append('line')
                .attr('stroke', '#999')
                .attr('stroke-opacity', 0.6);

            const node = svg.append('g')
                .selectAll('circle')
                .data(data.nodes)
                .enter().append('circle')
                .attr('class', 'node')
                .attr('r', 5)
                .attr('fill', {$colors}[0])
                .call(d3.drag()
                    .on('start', dragstarted)
                    .on('drag', dragged)
                    .on('end', dragended));

            simulation.on('tick', () => {
                link
                    .attr('x1', d => d.source.x)
                    .attr('y1', d => d.source.y)
                    .attr('x2', d => d.target.x)
                    .attr('y2', d => d.target.y);

                node
                    .attr('cx', d => d.x)
                    .attr('cy', d => d.y);
            });

            function dragstarted(event, d) {
                if (!event.active) simulation.alphaTarget(0.3).restart();
                d.fx = d.x;
                d.fy = d.y;
            }

            function dragged(event, d) {
                d.fx = event.x;
                d.fy = event.y;
            }

            function dragended(event, d) {
                if (!event.active) simulation.alphaTarget(0);
                d.fx = null;
                d.fy = null;
            }
        ";
    }

    private function generateSankeyDiagram($width, $height) {
        $colors = json_encode($this->options['colors']);
        $interactive = $this->options['interactive'] ? "
            .on('mouseover', function(event, d) {
                tooltip.transition().duration(200).style('opacity', 0.9);
                tooltip.html(d.source ? `From: \${d.source.name}<br>To: \${d.target.name}<br>Value: \${d.value}` : `\${d.name}: \${d.value}`)
                    .style('left', (event.pageX + 5) + 'px')
                    .style('top', (event.pageY - 28) + 'px');
            })
            .on('mouseout', function() {
                tooltip.transition().duration(500).style('opacity', 0);
            })
        " : "";
        return "
            const sankey = d3.sankey()
                .nodeWidth(20)
                .nodePadding(15)
                .size([width, height]);

            const graph = sankey({
                nodes: data.nodes.map(d => Object.assign({}, d)),
                links: data.links.map(d => Object.assign({}, d))
            });

            const link = svg.append('g')
                .selectAll('.link')
                .data(graph.links)
                .enter().append('path')
                .attr('class', 'link')
                .attr('d', d3.sankeyLinkHorizontal())
                .style('stroke', (d, i) => {$colors}[i % {$colors}.length])
                .style('stroke-width', d => Math.max(1, d.width))
                $interactive;

            const node = svg.append('g')
                .selectAll('.node')
                .data(graph.nodes)
                .enter().append('g')
                .attr('class', 'node')
                .attr('transform', d => `translate(\${d.x0}, \${d.y0})`);

            node.append('rect')
                .attr('height', d => d.y1 - d.y0)
                .attr('width', sankey.nodeWidth())
                .style('fill', (d, i) => {$colors}[i % {$colors}.length])
                $interactive;

            node.append('text')
                .attr('x', -6)
                .attr('y', d => (d.y1 - d.y0) / 2)
                .attr('dy', '0.35em')
                .attr('text-anchor', 'end')
                .text(d => d.name)
                .filter(d => d.x0 < width / 2)
                .attr('x', 6 + sankey.nodeWidth())
                .attr('text-anchor', 'start');
        ";
    }

    private function generateTreemap($width, $height) {
        $colors = json_encode($this->options['colors']);
        return "
            const root = d3.hierarchy(data)
                .sum(d => d.value);

            d3.treemap()
                .size([width, height])
                .padding(2)
                (root);

            svg.selectAll('rect')
                .data(root.leaves())
                .enter().append('rect')
                .attr('x', d => d.x0)
                .attr('y', d => d.y0)
                .attr('width', d => d.x1 - d.x0)
                .attr('height', d => d.y1 - d.y0)
                .attr('fill', (d, i) => {$colors}[i % {$colors}.length]);
        ";
    }

    public function saveToFile($filename) {
        return file_put_contents($filename, $this->render()) !== false;
    }
}

// تست کلاس
$data = [
    ['label' => 'A', 'value' => 30],
    ['label' => 'B', 'value' => 80],
    ['label' => 'C', 'value' => 45],
];

// تست نمودار میله‌ای
$barChart = new D3ChartBuilder('bar', $data, ['title' => 'نمودار میله‌ای نمونه', 'animation' => true, 'interactive' => true]);
$barChart->saveToFile('barchart.html');

// تست نمودار دایره‌ای
$pieChart = new D3ChartBuilder('pie', $data, ['title' => 'نمودار دایره‌ای نمونه']);
$pieChart->saveToFile('piechart.html');

// تست نمودار خطی با داده مناسب
$lineData = [
    ['x' => 0, 'y' => 10],
    ['x' => 1, 'y' => 20],
    ['x' => 2, 'y' => 15],
];
$lineChart = new D3ChartBuilder('line', $lineData, ['title' => 'نمودار خطی نمونه']);
$lineChart->saveToFile('linechart.html');

// تست نمودار پراکندگی با داده مناسب
$scatterChart = new D3ChartBuilder('scatter', $lineData, ['title' => 'نمودار پراکندگی نمونه']);
$scatterChart->saveToFile('scatterchart.html');

$dataForce = [
    'nodes' => [
        ['id' => 'A'], ['id' => 'B'], ['id' => 'C'],
    ],
    'links' => [
        ['source' => 'A', 'target' => 'B', 'value' => 4],
        ['source' => 'B', 'target' => 'C', 'value' => 2],
    ]
];
$forceChart = new D3ChartBuilder('force', $dataForce, ['title' => 'Force-Directed Graph']);
$forceChart->saveToFile('forcechart.html');

// تست نمودار Sankey با داده پیچیده‌تر
$dataSankey = [
    'nodes' => [
        ['name' => 'تولید برق'],    // 0
        ['name' => 'خورشیدی'],     // 1
        ['name' => 'بادی'],        // 2
        ['name' => 'خانه‌ها'],     // 3
        ['name' => 'کارخانه‌ها'],  // 4
        ['name' => 'ذخیره'],       // 5
    ],
    'links' => [
        ['source' => 1, 'target' => 0, 'value' => 50],  // خورشیدی -> تولید برق
        ['source' => 2, 'target' => 0, 'value' => 30],  // بادی -> تولید برق
        ['source' => 0, 'target' => 3, 'value' => 40],  // تولید برق -> خانه‌ها
        ['source' => 0, 'target' => 4, 'value' => 25],  // تولید برق -> کارخانه‌ها
        ['source' => 0, 'target' => 5, 'value' => 15],  // تولید برق -> ذخیره
    ]
];
$sankeyChart = new D3ChartBuilder('sankey', $dataSankey, [
    'title' => 'جریان انرژی',
    'width' => 800,
    'height' => 500,
    'interactive' => true,
]);
$sankeyChart->saveToFile('sankeychart.html');

$dataTreemap = [
    'name' => 'root',
    'children' => [
        ['name' => 'A', 'value' => 30],
        ['name' => 'B', 'value' => 80],
        ['name' => 'C', 'value' => 45],
    ]
];
$treemapChart = new D3ChartBuilder('treemap', $dataTreemap, ['title' => 'Treemap']);
$treemapChart->saveToFile('treemapchart.html');
?>