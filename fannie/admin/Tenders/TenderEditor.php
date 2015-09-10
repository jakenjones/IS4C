<?php
/*******************************************************************************

    Copyright 2010 Whole Foods Co-op

    This file is part of CORE-POS.

    CORE-POS is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    CORE-POS is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    in the file license.txt along with IT CORE; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*********************************************************************************/

include(dirname(__FILE__) . '/../../config.php');
if (!class_exists('FannieAPI')) {
    include($FANNIE_ROOT.'classlib2.0/FannieAPI.php');
}

class TenderEditor extends FannieRESTfulPage 
{
    protected $title = "Fannie : Tenders";
    protected $header = "Tenders";
    protected $must_authenticate = True;
    protected $auth_classes = array('tenders');
    public $description = '[Tenders] creates and updates tender types.';
    public $has_unit_tests = true;

    public function preprocess()
    {
        $this->addRoute('post<id><saveCode>');
        $this->addRoute('post<id><saveName>');
        $this->addRoute('post<id><saveType>');
        $this->addRoute('post<id><saveCMsg>');
        $this->addRoute('post<id><saveMin>');
        $this->addRoute('post<id><saveMax>');
        $this->addRoute('post<id><saveRLimit>');
        $this->addRoute('post<id><saveSalesCode>');
        $this->addRoute('post<newTender>');

        return parent::preprocess();
    }

    protected function getTenderModel($id=false)
    {
        $this->connection->selectDB($this->config->get('OP_DB')); 
        $model = new TendersModel($this->connection);
        if ($id !== false) {
            $model->TenderID($id);
        }

        return $model;
    }

    protected function post_id_saveCode_handler()
    {
        $this->connection->selectDB($this->config->get('OP_DB')); 
        $tester = new TendersModel($this->connection);
        $tester->TenderCode($this->saveCode);
        $tester->TenderID($this->id, '<>');
        if (count($tester->find()) > 0) {
            echo "Error: Code " . $this->saveCode . " is already in use";
        } else {
            $model = $this->getTenderModel($this->id);
            $model->TenderCode($this->saveCode);
            $model->save();
        }

        return false;
    }

    protected function post_id_saveName_handler()
    {
        $model = $this->getTenderModel($this->id);
        $model->TenderName($this->saveName);
        $model->save();

        return false;
    }

    protected function post_id_saveType_handler()
    {
        $model = $this->getTenderModel($this->id);
        $model->TenderType($this->saveType);
        $model->save();

        return false;
    }

    protected function post_id_saveCMsg_handler()
    {
        $model = $this->getTenderModel($this->id);
        $model->ChangeMessage($this->saveCMsg);
        $model->save();

        return false;
    }

    protected function post_id_saveMin_handler()
    {
        if (!is_numeric($this->saveMin)) {
            echo "Error: Minimum must be a number";
        } else {
            $model = $this->getTenderModel($this->id);
            $model->MinAmount($this->saveMin);
            $model->save();
        }

        return false;
    }

    protected function post_id_saveMax_handler()
    {
        if (!is_numeric($this->saveMax)) {
            echo "Error: Maximum must be a number";
        } else {
            $model = $this->getTenderModel($this->id);
            $model->MaxAmount($this->saveMax);
            $model->save();
        }

        return false;
    }

    protected function post_id_saveRLimit_handler()
    {
        if (!is_numeric($this->saveRLimit)) {
            echo "Error: Refund limit must be a number";
        } else {
            $model = $this->getTenderModel($this->id);
            $model->RefundLimit($this->saveRLimit);
            $model->save();
        }

        return false;
    }

    protected function post_id_saveSalesCode_handler()
    {
        if (!is_numeric($this->saveSalesCode)) {
            echo "Error: account # must be a number";
        } else {
            $model = $this->getTenderModel($this->id);
            $model->SalesCode($this->saveSalesCode);
            $model->save();
        }

        return false;
    }

    protected function post_newTender_handler()
    {
        $dbc = $this->connection;
        $dbc->selectDB($this->config->get('OP_DB'));
        $newID=1;
        $idQ = $dbc->prepare_statement("SELECT MAX(TenderID) FROM tenders");
        $idR = $dbc->exec_statement($idQ);
        if ($dbc->num_rows($idR) > 0){
            $idW = $dbc->fetch_row($idR);
            if (!empty($idW[0])) $newID = $idW[0] + 1;
        }

        $model = new TendersModel($dbc);
        $model->TenderID($newID);
        $model->TenderName('NEW TENDER');
        $model->TenderType('CA');
        $model->MinAmount(0);
        $model->MaxAmount(500);
        $model->MaxRefund(0);
        $model->save();
        
        echo getTenderTable();

        return false;
    }

    private function getTenderTable()
    {
        $this->connection->selectDB($this->config->get('OP_DB'));
        $model = new TendersModel($this->connection);
        
        $ret = '<table class="table">
            <tr><th>Code</th><th>Name</th><th>Change Type</th>
            <th>Change Msg</th><th>Min</th><th>Max</th>
            <th>Refund Limit</th><th>Account #</th></tr>';

        foreach($model->find('TenderID') as $row){
            $ret .= sprintf('<tr>
                <td><input size="2" maxlength="2" value="%s"
                    class="form-control"
                    onchange="saveCode.call(this, this.value,%d);" /></td>
                <td><input size="10" maxlength="255" value="%s"
                    class="form-control"
                    onchange="saveName.call(this, this.value,%d);" /></td>
                <td><input size="2" maxlength="2" value="%s"
                    class="form-control"
                    onchange="saveType.call(this, this.value,%d);" /></td>
                <td><input size="10" maxlength="255" value="%s"
                    class="form-control"
                    onchange="saveCMsg.call(this, this.value,%d);" /></td>
                <td class="col-sm-1"><div class="input-group">
                    <span class="input-group-addon">$</span>
                    <input size="6" maxlength="10" value="%.2f"
                    class="form-control price-field"
                    onchange="saveMin.call(this, this.value,%d);" />
                </div></td>
                <td class="col-sm-1"><div class="input-group">
                    <span class="input-group-addon">$</span>
                    <input size="6" maxlength="10" value="%.2f"
                    class="form-control price-field"
                    onchange="saveMax.call(this, this.value,%d);" />
                </div></td>
                <td class="col-sm-1"><div class="input-group"><span class="input-group-addon">$</span>
                    <input size="6" maxlength="10" value="%.2f"
                    class="form-control price-field"
                    onchange="saveRLimit.call(this, this.value,%d);" />
                </div></td>
                <td><input size="10" value="%s"
                    class="form-control"
                    onchange="saveSalesCode.call(this, this.value, %d);" /></td>
                </tr>',
                $row->TenderCode(),$row->TenderID(),
                $row->TenderName(),$row->TenderID(),
                $row->TenderType(),$row->TenderID(),
                $row->ChangeMessage(),$row->TenderID(),
                $row->MinAmount(),$row->TenderID(),
                $row->MaxAmount(),$row->TenderID(),
                $row->MaxRefund(),$row->TenderID(),
                $row->SalesCode(),$row->TenderID()
            );
        }
        $ret .= "</table>";
        $ret .= "<p>";
        $ret .= '<button type="button" class="btn btn-default" onclick="addTender();return false;">Add a new tender</button>';
        $ret .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $ret .= '<button type="button" class="btn btn-default" onclick="location=\'DeleteTenderPage.php\';">Delete a tender</button>';
        $ret .= '</p>';
        return $ret;
    }

    function javascript_content()
    {
        return <<<JAVASCRIPT
function saveCode(val,t_id){
    var elem = $(this);
    var orig = this.defaultValue;
    $.ajax({
        type:'post',
        data: 'saveCode='+val+'&id='+t_id,
        success: function(data){
            var timeout=1500;
            if (data == "") {
                data = 'Saved!';
            } else {
                elem.val(orig);
                timeout = 3000;
            }
            elem.popover({
                html: true,
                content: data,
                placement: 'auto bottom'
            });
            elem.popover('show');
            setTimeout(function(){elem.popover('destroy') }, timeout);
        }   
    });
}
function saveName(val,t_id){
    var elem = $(this);
    var orig = this.defaultValue;
    $.ajax({
        type:'post',
        data: 'saveName='+val+'&id='+t_id,
        success: function(data){
            var timeout=1500;
            if (data == "") {
                data = 'Saved!';
            } else {
                elem.val(orig);
                timeout = 3000;
            }
            elem.popover({
                html: true,
                content: data,
                placement: 'auto bottom'
            });
            elem.popover('show');
            setTimeout(function(){elem.popover('destroy') }, timeout);
        }
    });
}
function saveType(val,t_id){
    var elem = $(this);
    var orig = this.defaultValue;
    $.ajax({
        type:'post',
        data: 'saveType='+val+'&id='+t_id,
        success: function(data){
            var timeout=1500;
            if (data == "") {
                data = 'Saved!';
            } else {
                elem.val(orig);
                timeout = 3000;
            }
            elem.popover({
                html: true,
                content: data,
                placement: 'auto bottom'
            });
            elem.popover('show');
            setTimeout(function(){elem.popover('destroy') }, timeout);
        }   
    });
}
function saveCMsg(val,t_id){
    var elem = $(this);
    var orig = this.defaultValue;
    $.ajax({
        type:'post',
        data: 'saveCMsg='+val+'&id='+t_id,
        success: function(data){
            var timeout=1500;
            if (data == "") {
                data = 'Saved!';
            } else {
                elem.val(orig);
                timeout = 3000;
            }
            elem.popover({
                html: true,
                content: data,
                placement: 'auto bottom'
            });
            elem.popover('show');
            setTimeout(function(){elem.popover('destroy') }, timeout);
        }   
    });
}
function saveMin(val,t_id){
    var elem = $(this);
    var orig = this.defaultValue;
    $.ajax({
        type:'post',
        data: 'saveMin='+val+'&id='+t_id,
        success: function(data){
            var timeout=1500;
            if (data == "") {
                data = 'Saved!';
            } else {
                elem.val(orig);
                timeout = 3000;
            }
            elem.popover({
                html: true,
                content: data,
                placement: 'auto bottom'
            });
            elem.popover('show');
            setTimeout(function(){elem.popover('destroy') }, timeout);
        }   
    });
}
function saveMax(val,t_id){
    var elem = $(this);
    var orig = this.defaultValue;
    $.ajax({
        type:'post',
        data: 'saveMax='+val+'&id='+t_id,
        success: function(data){
            var timeout=1500;
            if (data == "") {
                data = 'Saved!';
            } else {
                elem.val(orig);
                timeout = 3000;
            }
            elem.popover({
                html: true,
                content: data,
                placement: 'auto bottom'
            });
            elem.popover('show');
            setTimeout(function(){elem.popover('destroy') }, timeout);
        }   
    });
}
function saveRLimit(val,t_id){
    var elem = $(this);
    var orig = this.defaultValue;
    $.ajax({
        type:'post',
        data: 'saveRLimit='+val+'&id='+t_id,
        success: function(data){
            var timeout=1500;
            if (data == "") {
                data = 'Saved!';
            } else {
                elem.val(orig);
                timeout = 3000;
            }
            elem.popover({
                html: true,
                content: data,
                placement: 'auto bottom'
            });
            elem.popover('show');
            setTimeout(function(){elem.popover('destroy') }, timeout);
        }   
    });
}
function saveSalesCode(val, t_id){
    var elem = $(this);
    var orig = this.defaultValue;
    $.ajax({
        type:'post',
        data: 'saveSalesCode='+val+'&id='+t_id,
        success: function(data){
            var timeout=1500;
            if (data == "") {
                data = 'Saved!';
            } else {
                elem.val(orig);
                timeout = 3000;
            }
            elem.popover({
                html: true,
                content: data,
                placement: 'auto bottom'
            });
            elem.popover('show');
            setTimeout(function(){elem.popover('destroy') }, timeout);
        }   
    });
}
function addTender(){
    $.ajax({
        cache:false,
        data:'newTender=yes',
        success: function(data){
            $('#mainDisplay').html(data);
        }
    });
}
JAVASCRIPT;
    }

    function get_view()
    {
        $ret = '<div id="alert-area"></div>';
        $ret .= '<div id="mainDisplay">';
        $ret .= $this->getTenderTable();
        $ret .= '</div>';
        return $ret;
    }

    public function helpContent()
    {
        return '<p>Tenders are different kinds of payment the store accepts.
            Each field saves when changed.</p>
            <ul>
                <li><em>Code</em> is the two letter code used by cashiers to enter
                the tender. These codes must be unique. While they are editable, using
                the defaults defined in sample tenders is recommended. In particular,
                changing CA, MI, CP, IC, EF, or FS could lead to oddities.</li>
                <li><em>Name</em> appears on screen and on receipt.</li>
                <li><em>Change Type</em> is the tender code used when the amount tendered
                exceeds the amount due resulting in a change line. Cash (CA) is
                most common.</li>
                <li><em>Change Msg</em> appears on screen and receipts for change lines.</li>
                <li><em>Min</em> and <em>Max</em> are soft limits. Attempting to tender 
                an amount outside that range results in a warning.</li>
                <li><em>Refund Limit</em> is a soft limit on the maximum allowed refund.
                Attempting to refund a larger amount results in a warning.</li>
                <li><em>Account #</em> is provided for accounting purposes. The value here
                will appear as a chart of accounts number in reports of tender activity.</li>
            </ul>';
    }

    /**
      Create a new tender
      Change & verify all fields of sample data tenderID=1
    */
    public function unitTest($phpunit)
    {
        $get = $this->get_view();
        $phpunit->assertNotEquals(0, strlen($get));

        $this->newTender = 1;
        $this->post_newTender_handler();
        $model = $this->getTenderModel(false);
        $model->TenderName('NEW TENDER');
        $phpunit->assertNotEquals(0, count($model->find()));

        $this->id = 1;
        $model = $this->getTenderModel(1);
        $phpunit->assertEquals(true, $model->load());

        $this->saveCode = 'ZZ';
        $phpunit->assertInternalType('bool', $this->post_id_saveCode_handler());
        $model->load();
        $phpunit->assertEquals('ZZ', $model->TenderCode());

        $this->saveName = 'Test Changed';
        $phpunit->assertInternalType('bool', $this->post_id_saveName_handler());
        $model->load();
        $phpunit->assertEquals('Test Changed', $model->TenderName());

        $this->saveType = 'YY';
        $phpunit->assertInternalType('bool', $this->post_id_saveType_handler());
        $model->load();
        $phpunit->assertEquals('YY', $model->TenderType());

        $this->saveCMsg = 'Kickbacks';
        $phpunit->assertInternalType('bool', $this->post_id_saveCMsg_handler());
        $model->load();
        $phpunit->assertEquals('Kickbacks', $model->ChangeMessage());

        $this->saveMin = 5;
        $phpunit->assertInternalType('bool', $this->post_id_saveMin_handler());
        $model->load();
        $phpunit->assertEquals(5, $model->MinAmount());

        $this->saveMax = 15;
        $phpunit->assertInternalType('bool', $this->post_id_saveMax_handler());
        $model->load();
        $phpunit->assertEquals(15, $model->MaxAmount());

        $this->saveRLimit = 25;
        $phpunit->assertInternalType('bool', $this->post_id_saveRLimit_handler());
        $model->load();
        $phpunit->assertEquals(25, $model->RefundLimit());

        $this->saveSalesCode = 2500;
        $phpunit->assertInternalType('bool', $this->post_id_saveSalesCode_handler());
        $model->load();
        $phpunit->assertEquals(2500, $model->SalesCode());
    }
}

FannieDispatch::conditionalExec();

