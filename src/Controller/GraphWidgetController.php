<?php

namespace Okvpn\Bundle\GraphWidgetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/")
 */
class GraphWidgetController extends Controller
{
    /**
     * @Route("/chart/widget/{widget}", name="okvpn_database_chart_widget", requirements={"widget"="[\w-]+"})
     *
     * @param string $widget
     * @return array
     *
     * @Template()
     */
    public function chartWidgetAction($widget)
    {
        $widgetAttributes = $this->get('oro_dashboard.widget_configs')
            ->getWidgetAttributesForTwig($widget);
        $queryOptions = $widgetAttributes['widgetConfiguration']['options']['value'];

        if ($queryOptions) {
            $chartData = $this->getChartsData($queryOptions['connection'] ?? '', $queryOptions['sql'] ?? '');
            if (\is_array($chartData)) {
                $widgetAttributes['chartView'] = $this->getGraphView($chartData[0], $chartData[1]);
            } else {
                $widgetAttributes['message'] = $chartData;
            }
        }


        return $widgetAttributes;
    }

    protected function getGraphView($xAxisType, $chartData)
    {
        $viewBuilder = $this->container->get('oro_chart.view_builder');
        $chartName = 'database_chart';

        $chartConfig = $this->get('oro_chart.config_provider')->getChartConfig($chartName);
        $chartConfig['data_schema']['label']['type'] = $xAxisType;
        $chartConfig['data_schema']['label']['default_type'] = $xAxisType;

        $view = $viewBuilder
            ->setArrayData($chartData)
            ->setOptions(
                array_merge_recursive(
                    [
                        'name' => $chartName
                    ],
                    $chartConfig
                )
            )
            ->getView();

        return $view;
    }

    /**
     * @param $connectionTransport
     * @param $sqlQuery
     * @return array|string
     */
    protected function getChartsData($connectionTransport, $sqlQuery)
    {
        $chartProvider = $this->container->get('okvpn_app.factory.database_charts_provider');

        try {
            list($format, $result) = $chartProvider->getChartData($connectionTransport, $sqlQuery);
            if ($format === null) {
                $format = 'integer';
            }
        } catch (\Exception  $exception) {
            return $exception->getMessage();
        }

        return [$format, $result];
    }
}
