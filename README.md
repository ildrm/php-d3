# D3ChartBuilder

`D3ChartBuilder` is a PHP class designed to simplify the implementation of D3.js charts. This class allows you to generate the necessary HTML, CSS, and JavaScript code for rendering charts by providing data and configuration options in PHP. Inspired by the D3.js Gallery (https://observablehq.com/@d3/gallery), it offers high flexibility to support a wide variety of chart types.

## Features

- **Ease of Use:** Generate complex D3.js charts with minimal effort using PHP.
- **Modular Design:** Supports multiple chart types through separate methods, making it easy to extend.
- **Customizable:** Provides options for dimensions, colors, animations, interactivity, and more.
- **Interactive Elements:** Optional tooltips for enhanced user interaction.
- **Dependency Management:** Automatically includes required D3.js libraries based on chart type.

## Requirements

- PHP 7.0 or higher
- Internet connection (for loading D3.js and additional modules from CDN)

## Installation

1. Save the `D3ChartBuilder.php` file in your project directory.
2. Include the class in your PHP script:

```php
require_once 'D3ChartBuilder.php';
```

Usage
Basic Structure

To create a chart, instantiate the D3ChartBuilder class with the chart type, data, and optional configuration settings, then render or save the output.
```php
$data = [
    ['label' => 'A', 'value' => 30],
    ['label' => 'B', 'value' => 80],
    ['label' => 'C', 'value' => 45],
];
$chart = new D3ChartBuilder('bar', $data, ['title' => 'Sample Bar Chart']);
$chart->saveToFile('barchart.html'); // Saves to a file
// OR
echo $chart->render(); // Outputs directly to the browser
```
Configuration Options

The $options array allows customization of the chart:

    width (int): Width of the chart in pixels (default: 600).
    height (int): Height of the chart in pixels (default: 400).
    margin (array): Margins around the chart (top, right, bottom, left) (default: [20, 20, 30, 40]).
    colors (array): Array of colors for chart elements (default: ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b']).
    title (string): Chart title (default: "Chart [ChartType]").
    animation (bool): Enable animation (default: false).
    interactive (bool): Enable tooltips and interactivity (default: false).

Example with customization:
```php
$options = [
    'width' => 800,
    'height' => 500,
    'colors' => ['#ff9999', '#66b3ff', '#99ff99'],
    'title' => 'Custom Chart',
    'animation' => true,
    'interactive' => true,
];
$chart = new D3ChartBuilder('pie', $data, $options);
$chart->saveToFile('piechart.html');
```
Supported Chart Types

The class currently supports the following chart types:

    Bar Chart (bar):
        Displays vertical bars.
        Data format: [['label' => string, 'value' => number], ...]
    Line Chart (line):
        Displays a continuous line connecting points.
        Data format: [['x' => number, 'y' => number], ...]
    Scatter Plot (scatter):
        Displays individual data points.
        Data format: [['x' => number, 'y' => number], ...]
    Pie Chart (pie):
        Displays a circular chart divided into segments.
        Data format: [['label' => string, 'value' => number], ...]
    Area Chart (area):
        Displays an area under a line.
        Data format: [['x' => number, 'y' => number], ...]
    Box Plot (box):
        Displays statistical data (quartiles).
        Data format: [['group' => string, 'values' => ['q1' => number, 'q3' => number]], ...]
    Force-Directed Graph (force):
        Displays a network of nodes and links with force simulation.
        Data format: ['nodes' => [['id' => string], ...], 'links' => [['source' => string, 'target' => string, 'value' => number], ...]]
    Sankey Diagram (sankey):
        Displays flow between nodes.
        Data format: ['nodes' => [['name' => string], ...], 'links' => [['source' => int, 'target' => int, 'value' => number], ...]]
    Treemap (treemap):
        Displays hierarchical data as nested rectangles.
        Data format: ['name' => string, 'children' => [['name' => string, 'value' => number], ...]]

Examples
Bar Chart with Animation and Interactivity
```php
$data = [
    ['label' => 'A', 'value' => 30],
    ['label' => 'B', 'value' => 80],
    ['label' => 'C', 'value' => 45],
];
$chart = new D3ChartBuilder('bar', $data, [
    'title' => 'Animated Bar Chart',
    'animation' => true,
    'interactive' => true,
]);
$chart->saveToFile('barchart.html');
```
Complex Sankey Diagram
```php
$dataSankey = [
    'nodes' => [
        ['name' => 'Power Generation'],
        ['name' => 'Solar'],
        ['name' => 'Wind'],
        ['name' => 'Homes'],
        ['name' => 'Factories'],
        ['name' => 'Storage'],
    ],
    'links' => [
        ['source' => 1, 'target' => 0, 'value' => 50], // Solar -> Power Generation
        ['source' => 2, 'target' => 0, 'value' => 30], // Wind -> Power Generation
        ['source' => 0, 'target' => 3, 'value' => 40], // Power Generation -> Homes
        ['source' => 0, 'target' => 4, 'value' => 25], // Power Generation -> Factories
        ['source' => 0, 'target' => 5, 'value' => 15], // Power Generation -> Storage
    ]
];
$sankeyChart = new D3ChartBuilder('sankey', $dataSankey, [
    'title' => 'Energy Flow',
    'width' => 800,
    'height' => 500,
    'interactive' => true,
]);
$sankeyChart->saveToFile('sankeychart.html');
```
Treemap
```php
$dataTreemap = [
    'name' => 'root',
    'children' => [
        ['name' => 'A', 'value' => 30],
        ['name' => 'B', 'value' => 80],
        ['name' => 'C', 'value' => 45],
    ]
];
$treemapChart = new D3ChartBuilder('treemap', $dataTreemap, ['title' => 'Treemap Example']);
$treemapChart->saveToFile('treemapchart.html');
```
Adding New Chart Types

To add a new chart type:

    Create a new method in the class (e.g., generateNewChartType).
    Add the D3.js code for the chart type inside the method.
    Update the generateJavaScript method to include the new type in the switch statement.

Example:
```php
private function generateNewChartType($width, $height) {
    return "/* D3.js code for new chart type */";
}
```

Then in generateJavaScript:
```php
case 'newchart':
    $chartCode = $this->generateNewChartType($width, $height);
    break;
```
Troubleshooting

    Chart Not Displaying: Check the browser console (F12 -> Console) for JavaScript errors. Ensure an internet connection is available for CDN scripts.
    Data Format Issues: Verify that the data matches the expected format for the chart type.
    Dependencies: For charts like Sankey, ensure the additional module (e.g., d3-sankey) loads correctly.

License

This project is open-source and available under the MIT License.
Contributing

Feel free to submit pull requests or issues to improve the class or add new chart types!