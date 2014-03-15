<?php
$this->Html->script($jsRoot.'silly.js', array('block' => 'script'));
$this->Html->script($jsRoot.'tests/example1.test.js', array('block' => 'script'));

$this->Html->script($jsRoot.'cor-blimey.js', array('block' => 'script'));
$this->Html->script($jsRoot.'tests/example2.test.js', array('block' => 'script'));
?>
<h1 id="qunit-header">All Javascript Tests</h1>