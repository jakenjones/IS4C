<?php

/**
 * @backupGlobals disabled
 */
class PagesFannieTest extends PHPUnit_Framework_TestCase
{
    public function testReports()
    {
        $reports = FannieAPI::listModules('FannieReportPage', true);
        $config = FannieConfig::factory();
        $logger = new FannieLogger();
        $op_db = $config->get('OP_DB');
        $dbc = FannieDB::forceReconnect($op_db);
        $dbc->throwOnFailure(true);
        if (strstr($config->get('SERVER_DBMS'), 'mysql')) {
            $dbc->query("SET SESSION sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
        }

        $reports[] = 'AuthReport';
        if (!class_exists('AuthReport', false)) {
            include(__DIR__ . '/../../fannie/auth/ui/AuthReport.php');
        }

        foreach ($reports as $report_class) {
            $obj = new $report_class();
            $obj->setConfig($config);
            $obj->setLogger($logger);
            $dbc->selectDB($op_db);
            $obj->setConnection($dbc);
            $obj = FannieDispatch::twig($obj);

            $pre = $obj->preprocess();
            $this->assertInternalType('boolean',$pre);

            $auth = $obj->checkAuth();
            $this->assertInternalType('boolean',$pre);

            $html_form = $obj->form_content();
            $this->assertNotEquals(0, strlen($html_form), 'Report form is empty for ' . $report_class);

            $form = new \COREPOS\common\mvc\ValueContainer();
            foreach ($obj->requiredFields() as $field) {
                if (strstr($field, 'date')) {
                    $form->$field = date('Y-m-d');
                } else {
                    $form->$field = 1;
                }
            }
            $obj->setForm($form);
            $preamble = $obj->report_description_content();
            $this->assertInternalType('array', $preamble, 'Report did not return description content ' . $report_class);
            $results = $obj->fetch_report_data();
            $this->assertInternalType('array', $results, 'Report did not return results ' . $report_class);
        }
    }

    public function testPages()
    {
        $pages = FannieAPI::listModules('FanniePage', true);
        $pages[] = 'COREPOS\\Fannie\\API\\FannieCRUDPage';
        $config = FannieConfig::factory();
        $logger = new FannieLogger();
        $op_db = $config->get('OP_DB');
        $dbc = FannieDB::get($op_db);
        $dbc->throwOnFailure(true);
        if (strstr($config->get('SERVER_DBMS'), 'mysql')) {
            $dbc->query("SET SESSION sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
        }

        $speed = array();
        foreach ($pages as $page_class) {
            $obj = new $page_class();
            $obj->setConfig($config);
            $obj->setLogger($logger);
            $dbc->selectDB($op_db);
            $obj->setConnection($dbc);
            $obj = FannieDispatch::twig($obj);
            if ($page_class == 'WfcHtViewSalaryPage') continue; // header/redirect problem

            ob_start();
            $pre = $obj->preprocess();
            ob_get_clean();
            $this->assertInternalType('boolean',$pre);

            $help = $obj->helpContent();
            $this->assertInternalType('string', $help);

            $auth = $obj->checkAuth();
            $this->assertInternalType('boolean',$pre);

            $t1 = microtime(true);
            $obj->unitTest($this);
            $elapse = microtime(true)-$t1;
            $speed[$page_class] = $elapse;
        }
        arsort($speed);
        var_dump($speed);
    }

    public function testBase()
    {
        $obj = new FanniePage();
        $config = FannieConfig::factory();
        $logger = new FannieLogger();
        $op_db = $config->get('OP_DB');
        $dbc = FannieDB::get($op_db);
        $obj->setConfig($config);
        $obj->setLogger($logger);
        $dbc->selectDB($op_db);
        $obj->setConnection($dbc);
        
        $this->assertNotEquals(0, strlen($obj->getHeader()));
        $this->assertEquals($obj->checkAuth(), $obj->check_auth());
        $this->assertNotEquals(0, $obj->getFooter());

        $obj = new FannieReportPage();
        $obj->setConfig($config);
        $obj->setLogger($logger);
        $dbc->selectDB($op_db);
        $obj->setConnection($dbc);
        $obj->baseUnitTest($this);

        $obj = new COREPOS\Fannie\API\FannieCRUDPage();
        $obj->setConfig($config);
        $obj->setLogger($logger);
        $dbc->selectDB($op_db);
        $obj->setConnection($dbc);
        $obj->baseUnitTest($this);

        $obj = new COREPOS\Fannie\API\InstallPage();
        $obj->setConfig($config);
        $obj->setLogger($logger);
        $dbc->selectDB($op_db);
        $obj->setConnection($dbc);
        /*
        $obj->themed = true;
        $this->assertNotEquals(0, strlen($obj->getHeader()));
        $this->assertNotEquals(0, strlen($obj->getFooter()));
        $config->set('FANNIE_WINDOW_DRESSING', true);
        $obj->themed = false;
        $this->assertNotEquals(0, strlen($obj->getHeader()));
        $this->assertNotEquals(0, strlen($obj->getFooter()));
        $config->set('FANNIE_WINDOW_DRESSING', false);
        $this->assertNotEquals(0, strlen($obj->getHeader()));
        $this->assertNotEquals(0, strlen($obj->getFooter()));
        */
    }

    public function testMisc()
    {
        if (!class_exists('SigImage')) {
            include(dirname(__FILE__) . '/../../fannie/admin/LookupReceipt/SigImage.php');
        }
        $s = new SigImage();
        $s->unitTest($this);
    }
}

