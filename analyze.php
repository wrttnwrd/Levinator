<?php include 'header.php'; ?>

<?php
// this function does the heavy lifting
function levenshtein2($str1, $str2, $cost_ins = null, $cost_rep = null, $cost_del = null) {
    $d = array_fill(0, strlen($str1) + 1, array_fill(0, strlen($str2) + 1, 0));
    $ret = 0;
        
    for ($i = 1; $i < strlen($str1) + 1; $i++)
        $d[$i][0] = $i;
    for ($j = 1; $j < strlen($str2) + 1; $j++)
        $d[0][$j] = $j;
        
    for ($i = 1; $i < strlen($str1) + 1; $i++)
        for ($j = 1; $j < strlen($str2) + 1; $j++) {
            $c = 1;
            if ($str1{$i - 1} == $str2{$j - 1})
                $c = 0;
            $d[$i][$j] = min($d[$i - 1][$j] + 1, $d[$i][$j - 1] + 1, $d[$i - 1][$j - 1] + $c);
            $ret = $d[$i][$j];
        }
    
    return $ret;
}
?>

<?php
// This code takes the form submission, creates two arrays, then loops through - it is very brute force.
// You could do better with a tool like Lucene, which is what we use at Portent for big jobs

$bads = explode("\n", $_POST['badurls']); 
$goods = explode("\n", $_POST['goodurls']); 

$badcount = count($bads);
$goodcount = count($goods);

$fixlist = '';

if ($badcount > 100) {
	$bads = array_splice($bads,0,100);
}

if ($goodcount > 100) {
	$goods = array_splice($goods,0,100);
}


// no shortest distance found, yet
$shortest = -1;
$counter = 0;
$listcounter = 0;
foreach ($bads as $bad) {  
	$counter = $counter + 1;
	// loop through words to find the closest
	foreach ($goods as $good) {
		$listcounter = $listcounter + 1;
	    // calculate the distance between the input word,
	    // and the current word
	    $lev = levenshtein2($bad, $good);
	    // if this distance is less than the next found shortest
	    // distance, OR if a next shortest word has not yet been found
	    if ($lev <= $shortest || $shortest < 0) {
	        // set the closest match, and shortest distance
	        $closest  = $good;
	        $shortest = $lev;
	    }
	}
		$bad = trim($bad);
		$closest = trim($closest);
		$bad = str_replace("\r", "", $bad);
		$badl = str_replace("\n", "", $bad);
		
		$closest = str_replace("\r", "", $closest);
		$goodl = str_replace("\n", "", $closest);

		
		$fixlist = $fixlist.'redirect 301 '.$badl.' '.$goodl."\n";
		$shortest = -1;
		$closest = '';
		$listcounter = 0;
	}

?>



<div class="content-box">
								<label  class="desc">
									That's it. Cut and paste the result below. It's already written for an .htaccess file, if that's what you use.
								</label>

<textarea tabindex="2" cols="50" rows="10" class="field textarea" name="badurls"  style="width:715px;">
<?php echo $fixlist; ?>
</textarea>
<a href="index.php">K, that was cool. Do it again!</a> <br />
This code written by a lot of people, and assembled by <a href="http://www.portentinteractive.com">Portent Interactive</a>
<?php include 'footer.php'; ?>