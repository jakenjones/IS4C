<?php
/*******************************************************************************

    Copyright 2015 Whole Foods Co-op

    This file is part of CORE-POS.

    IT CORE is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    IT CORE is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    in the file license.txt along with IT CORE; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*********************************************************************************/

/**
  @class PriceRulesModel
*/
class PriceRulesModel extends BasicModel
{
    protected $name = "PriceRules";

    protected $columns = array(
    'priceRuleID' => array('type'=>'INT', 'primary_key'=>true, 'increment'=>true),
    'priceRuleTypeID' => array('type'=>'INT'),
    'minMargin' => array('type'=>'DOUBLE', 'default'=>0),
    'maxPrice' => array('type'=>'DOUBLE', 'default'=>0),
    'reviewDate' => array('type'=>'DATETIME'),
    'details' => array('type'=>'TEXT'),
    );

    public function doc()
    {
        return '
Use:
Price Rules define reasons for making exceptions to normal margin. A price rule
type might be "loss leader" or "competitor match". Attaching a reason to a given
item provides more information why the margin is so low (or high).
            ';
    }
}

