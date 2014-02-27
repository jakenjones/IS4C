<?php
/*******************************************************************************

    Copyright 2012 Whole Foods Co-op

    This file is part of IT CORE.

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

class Paycards extends Plugin {

	public $description = 'Plugin for integrated payment cards';

	public $plugin_settings = array(
		'ccLive' => array(
		'label' => 'Live',
		'description' => 'Enable live integrated transactions',
		'default' => 1,
		'options' => array(
			'Yes' => 1,
			'No' => 0
			)
		),
        'PaycardsTerminalID' => array(
        'label' => 'Terminal ID',
        'description' => 'Unique ID for MC regs (1-3 characters, alphanumeric)',
        'default'=> '',
        ),
		'PaycardsCashierFacing' => array(
		'label' => 'Mode',
		'description' => 'Who is swiping the card?',
		'default' => 1,
		'options' => array(
			'Cashier' => 1,
			'Customer' => 0
			)
		),
		'PaycardsStateChange' => array(
		'label' => 'Communication',
		'description' => 'Should terminal switch screens 
based on direct input or
messages from POS?',
		'default' => 'direct',
		'options' => array(
			'Direct Input' => 'direct',
			'Messages' => 'coordinated' 
			)
		),
		'PaycardsOfferCashBack' => array(
		'label' => 'Offer Cashback',
		'description' => 'Show cashback screen on terminal',
		'default' => 1,
		'options' => array(
			'Yes' => 1,
			'No' => 0
			)
		),
        'PaycardsAllowEBT' => array(
            'label' => 'Allow EBT',
            'description' => 'Show EBT option on terminal 
                              (only works with Communication type Messages)',
            'default' => 1,
            'options' => array(
                'Yes' => 1,
                'No' => 0
                )
        ),
        'PaycardsBlockTenders' => array(
            'label' => 'Block Other Tenders',
            'description' => 'If customer card data is available, do not
                              allow other tenders',
            'default' => 0,
            'options' => array(
                'Yes' => 1,
                'No' => 0
                )
        ),
        'PaycardsBlockExceptions' => array(
            'label' => 'Blocking Exceptions',
            'description' => 'Still allow these tenders with Block Other Tenders enabled',
            'default' => 'CP IC',
        ),
	);

	public function plugin_enable(){

	}

	public function plugin_disable(){

	}

}
