<?php

namespace COREPOS\pos\lib\ReceiptBuilding\Messages;
use COREPOS\pos\lib\Database;
use COREPOS\pos\lib\ReceiptLib;
use \CoreLocal;

class WicReceiptMessage extends ReceiptMessage
{
    public $standalone_receipt_type = 'wicSlip';

    public function standalone_receipt($ref, $reprint=False)
    {
        return 
            ReceiptLib::centerString("................................................")."\n"
            . ReceiptLib::centerString("B A L A N C E") . "\n"
            . ReceiptLib::centerString("................................................")."\n"
            . $this->message(1, $ref, $reprint)
            . ReceiptLib::centerString("................................................")."\n"
            . ReceiptLib::centerString("E L I G I B L E    I T E M S") . "\n"
            . ReceiptLib::centerString("................................................")."\n"
            . $this->potentialItems(CoreLocal::get('EWicBalance'))
            . ReceiptLib::centerString("................................................")."\n";
    }

    private function potentialItems($wicData)
    {
        $ret = "";
        $categories = array('cat'=>array(), 'sub'=>array());
        foreach ($wicData as $balanceRecord) {
            $categories['cat'][$balanceRecord['cat']['eWicCategoryID']] = true;
            if (isset($balanceRecord['subcat'])) {
                $categories['sub'][$balanceRecord['subcat']['eWicSubCategoryID']] = true;
            }
        }

        $dbc = Database::tDataConnect();
        $res = $dbc->query('SELECT *
            FROM localtemptrans AS t
                INNER JOIN ' . CoreLocal::get('pDatabase') . $dbc->sep() . 'EWicItems AS e ON t.upc=e.upc
            ORDER BY t.trans_id');
        while ($row = $dbc->fetchRow($res)) {
            if ($row['eWicCategoryID'] && isset($categories['cat'][$row['eWicCategoryID']])) {
                $ret .= $row['description'] . "\n";
            }
        }

        return $ret;
    }

    public function select_condition()
    {
        return "MAX(CASE WHEN trans_subtype IN ('EW') THEN trans_id ELSE 0 END)";
    }

    public function message($val, $ref, $reprint=False)
    {
        $date = date('Ymd');
        list($emp, $reg, $trans) = ReceiptLib::parseRef($ref);
        $slip = '';

        $dbc = Database::tDataConnect();
        if ($reprint) {
            $dbc = Database::mDataConnect();
            if ($dbc === false) {
                return '';
            }
        }

        $transType = $dbc->concat('p.cardType', "' '", 'p.transType', '');

        $query = "SELECT p.amount,
                    p.name,
                    p.PAN,
                    p.refNum,
                    $transType AS ebtMode,
                    p.xResultMessage,
                    p.xTransactionID,
                    p.xBalance,
                    p.requestDatetime AS datetime
                  FROM PaycardTransactions AS p
                  WHERE dateID=" . date('Ymd') . "
                    AND empNo=" . $emp . "
                    AND registerNo=" . $reg . "
                    AND transNo=" . $trans . "
                    AND p.validResponse=1
                    AND p.xResultMessage LIKE '%APPROVE%'
                    AND p.cardType = 'EWIC'
                  ORDER BY p.requestDatetime";
        $result = $dbc->query($query);
        $prevRefNum = false;
        while ($row = $dbc->fetchRow($result)) {

            // failover to mercury's backup server can
            // result in duplicate refnums. this is
            // by design (theirs, not CORE's)
            if ($row['refNum'] == $prevRefNum) {
                continue;
            }
            $slip .= ReceiptLib::centerString("................................................")."\n";
            // store header
            for ($i=1; $i<= CoreLocal::get('chargeSlipCount'); $i++) {
                $slip .= ReceiptLib::centerString(CoreLocal::get("chargeSlip" . $i))."\n";
            }
            $slip .= "\n";
            $col1 = array();
            $col2 = array();
            $col1[] = $row['ebtMode'];
            $col2[] = "Entry Method: swiped\n";
            $col1[] = "Sequence: " . $row['xTransactionID'];
            $col2[] = "Card: ".$row['PAN'];
            $col1[] = "Authorization: " . $row['xResultMessage'];
            $col2[] = ReceiptLib::boldFont() . "Amount: " . $row['amount'] . ReceiptLib::normalFont();
            $slip .= ReceiptLib::twoColumns($col1, $col2);

            $slip .= ReceiptLib::centerString("................................................")."\n";

            $prevRefNum = $row['refNum'];
        }

        $dbc = Database::tDataConnect();
        $emvP = $dbc->prepare('
            SELECT content
            FROM EmvReceipt
            WHERE dateID=?
                AND empNo=?
                AND registerNo=?
                AND transNo=?
                AND content LIKE \'%Benefits expire%\'
            ORDER BY transID DESC
        ');
        $balance = $dbc->getValue($emvP, array($date, $emp, $reg, $trans));
        $slip .= $balance ? $balance : '';

        return $slip;
    }
}

