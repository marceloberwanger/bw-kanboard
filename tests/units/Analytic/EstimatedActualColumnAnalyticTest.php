<?php

namespace KanboardTests\units\Analytic;

use KanboardTests\units\Base;
use Kanboard\Model\TaskCreationModel;
use Kanboard\Model\ProjectModel;
use Kanboard\Analytic\EstimatedActualColumnAnalytic;

class EstimatedActualColumnAnalyticTest extends Base
{
    public function testBuildRoundsHours()
    {
        $taskCreationModel = new TaskCreationModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $estimatedActualColumnAnalytic = new EstimatedActualColumnAnalytic($this->container);

        $this->assertEquals(1, $projectModel->create(array('name' => 'test1')));

        // MySQL stores hours as FLOAT: without rounding, these sums come back as 1.7400000095367432 and 0.9399999976158142
        $this->assertNotFalse($taskCreationModel->create(array('project_id' => 1, 'title' => 'test', 'time_estimated' => 0.64, 'time_spent' => 0.64)));
        $this->assertNotFalse($taskCreationModel->create(array('project_id' => 1, 'title' => 'test', 'time_estimated' => 1.1, 'time_spent' => 0.3)));

        $metrics = $estimatedActualColumnAnalytic->build(1);
        $this->assertSame(1.74, $metrics[1]['hours_estimated']);
        $this->assertSame(0.94, $metrics[1]['hours_spent']);
        $this->assertSame(0, $metrics[2]['hours_spent']);
    }
}
