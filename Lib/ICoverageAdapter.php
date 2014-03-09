<?php
interface ICoverageAdapter {
	
	public function execute($noInstrument, $exclude, $sourceDir, $targetDir);
	
}