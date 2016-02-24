<ul>
	<?php foreach($invoices as $invoice) {?>
		<?php $totals = $invoice->getTotals()?>
		<?php if($invoice->error) {?>
			<li class="fail">
				<span><?php echo htmlspecialchars($invoice->filename)?>.pdf</span>
				<span>ERROR: <?php echo htmlspecialchars($invoice->error)?></span>
				<span class="wrapper"><i id="slide">Failed</i></span>
			</li>
		<?php } else {?>
			<li>
				<span>
					<a href="#" class="save">
						<svg x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve">
							<path fill="#F3F4F5" d="M13,8v3c0,1.1-0.9,2-2,2H3c-1.1,0-2-0.9-2-2V8H0v3c0,1.6,1.4,3,3,3h8c1.6,0,3-1.4,3-3V8H13z"/>
							<polygon fill="#F3F4F5" points="11,4.7 10.3,4 7.5,6.8 7.5,0.3 6.5,0.3 6.5,6.8 3.7,4 3,4.7 7,8.7 "/>
						</svg>
					</a>
				</span>
				<span><a href="#" class="view"><?php echo htmlspecialchars($invoice->filename)?>.pdf</a></span>
				<span><?php echo htmlspecialchars($invoice->getCustomerName())?></span>
				<span>&pound;<?php echo number_format($totals["grand"], 2)?></span>
				<span title="line items"><?php echo count($invoice->lines)?> items</span>
				<span class="wrapper"><i id="slide">OK</i></span>
			</li>
		<?php }?>
	<?php }?>	
</ul>
