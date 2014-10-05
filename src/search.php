<?php
header('Content-Type: text/html; charset=utf-8');

require_once('Apache/Solr/Service.php');

$unique_key = "url";
$default_start = 0;
$default_rows = 10;
$default_fq = array();
$default_fl = "*,score";
$default_facet = "true";
$default_facet_field = array("genre");
$default_facet_mincount = 1;
$default_hl = "true";
$default_hl_fl = "title,summary";
$default_hl_snipets = 2;
$default_hl_simple_pre = "<strong>";
$default_hl_simple_post = "</strong>";

$start = isset($_REQUEST['start']) ? $_REQUEST['start'] : $default_start;
$rows = isset($_REQUEST['rows']) ? $_REQUEST['rows'] : $default_rows;
$query = $_REQUEST['q'];
$fq = isset($_REQUEST['fq']) ? $_REQUEST['fq'] : $default_fq;
$fl = isset($_REQUEST['fl']) ? $_REQUEST['fl'] : $default_fl;
$facet = isset($_REQUEST['facet']) ? $_REQUEST['facet'] : $default_facet;
$facet_field = isset($_REQUEST['facet_field']) ? $_REQUEST['facet_field'] : $default_facet_field;
$facet_mincount = isset($_REQUEST['facet_mincount']) ? $_REQUEST['facet_mincount'] : $default_facet_mincount;
$hl = isset($_REQUEST['hl']) ? $_REQUEST['hl'] : $default_hl;
$hl_fl = isset($_REQUEST['hl_fl']) ? $_REQUEST['hl_fl'] : $default_hl_fl;
$hl_snipets = isset($_REQUEST['hl_snipets']) ? $_REQUEST['hl_snipets'] : $default_hl_snipets;
$hl_simple_pre = isset($_REQUEST['hl_simple_pre']) ? $_REQUEST['hl_simple_pre'] : $default_hl_simple_pre;
$hl_simple_post = isset($_REQUEST['hl_simple_post']) ? $_REQUEST['hl_simple_post'] : $default_hl_simple_post;
$result = null;

$params = array(
	'fq' => $fq,
	'fl' => $fl,
	'facet' => $facet,
	'facet.field' => $facet_field,
	'facet.mincount' => $facet_mincount,
	'hl' => $hl,
	'hl.fl' => $hl_fl,
	'hl.simple.pre' => $hl_simple_pre,
	'hl.simple.post' => $hl_simple_post,
);

if ($query) {
	$solr = new Apache_Solr_Service('localhost', 8983, '/solr/solrbook');
	try {
		$result = $solr->search($query, $start, $rows, $params);
	} catch (Exception $e) {
		die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
	}
}
?>

<html>
	<head>
		<link rel="stylesheet" href="css/search.css" />
        <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
		<title>PHP Solr Client Example</title>
	</head>
	<body>
		<div id="container"><!-- start container -->
			<div id="head"><!-- start head -->
				PHP Solr Client Example
				<div id="searchbox"><!-- start searchbox -->
					<form accept-charset="utf-8" method="get" class="form-search">
						<label for="q">Search:</label>
						<input id="q" name="q" size="100" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" placeholder="キーワードを入力"/>
						<input id="start" name="start" type="hidden" value="<?php echo htmlspecialchars($default_start, ENT_QUOTES, 'utf-8'); ?>"/>
						<input id="rows" name="rows" type="hidden" value="<?php echo htmlspecialchars($rows, ENT_QUOTES, 'utf-8'); ?>"/>
						<input id="fl" name="fl" type="hidden" value="<?php echo htmlspecialchars($fl, ENT_QUOTES, 'utf-8'); ?>"/>
						<input id="facet" name="facet" type="hidden" value="<?php echo htmlspecialchars($facet, ENT_QUOTES, 'utf-8'); ?>"/>
<?php
foreach ($facet_field as $ff) {
?>
						<input id="facet_field" name="facet.field[]" type="hidden" value="<?php echo htmlspecialchars($ff, ENT_QUOTES, 'utf-8'); ?>"/>
<?php
}
?>
						<input id="facet_mincount" name="facet.mincount" type="hidden" value="<?php echo htmlspecialchars($facet_mincount, ENT_QUOTES, 'utf-8'); ?>"/>
						<input id="hl" name="hl" type="hidden" value="<?php echo htmlspecialchars($hl, ENT_QUOTES, 'utf-8'); ?>"/>
						<input id="hl_fl" name="hl.fl" type="hidden" value="<?php echo htmlspecialchars($hl_fl, ENT_QUOTES, 'utf-8'); ?>"/>
						<input id="hl_simple_pre" name="hl.simple.pre" type="hidden" value="<?php echo htmlspecialchars($hl_simple_pre, ENT_QUOTES, 'utf-8'); ?>"/>
						<input id="hl_simple_post" name="hl.simple.post" type="hidden" value="<?php echo htmlspecialchars($hl_simple_post, ENT_QUOTES, 'utf-8'); ?>"/>
						<input type="submit" class="btn btn-primary"/>
					</form>
				</div><!-- end searchbox -->
			</div><!-- end head -->
			
			<div id="main"><!-- start main -->
				<div id="searchresult"><!-- start searchresult -->
<?php
if ($result) {
	$total_num = (int)$result->response->numFound;
	$disp_start_num = min($start + 1, $total_num);
	$disp_end_num = min($start + $rows, $total_num);
?>
					<div id="pageinfo"><!-- start pageinfo -->
						Results <?php echo $disp_start_num ?> - <?php echo $disp_end_num?> of <?php echo $total_num ?>:
					</div><!-- end pageinfo -->
<?php
	foreach ($result->response->docs as $doc) {
?>
					<div id="document"><!-- start document -->
<?php
		$key = "";
		foreach ($doc as $field => $value) {
			if ($field === $unique_key) {
				$key = $value;
			}
		}
		foreach ($doc as $field => $value) {
?>
						<div id="field"><!-- start field -->
							<span id="fieldname"><!-- start fieldname -->
								<?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?>:&nbsp;
							</span><!-- end fieldname -->
							<span id="fieldvalue"><!-- start fieldvalue -->
<?php
			$highlight_text = "";
			$highlighting = array();
			for ($i = 0; $i < $hl_snipets; $i++) {
				if (!empty($result->highlighting->{$key})) {
					$h = $result->highlighting->{$key}->{$field}[$i];
					if (!empty($h)) {
						array_push($highlighting, $h);
					}
				}
			}
			$highlight_text = join(' ... ', $highlighting);
			
			if (!empty($highlight_text)) {
?>
								<?php echo $highlight_text; ?>
<?php
			} else if (is_array($value)) {
?>
								<?php echo htmlspecialchars(join(', ', $value), ENT_NOQUOTES, 'utf-8'); ?>
<?php
			} else {
?>
								<?php echo htmlspecialchars((string)$value, ENT_NOQUOTES, 'utf-8'); ?>
<?php
			}
?>
							</span><!-- end fieldvalue -->
						</div><!-- end field -->
<?php
		}
?>
					</div><!-- end document -->
<?php
	}
?>
					<div id="pagination"><!-- start pagenation -->
<?php
	$wsiz = 10;
	$w1 = 5;
	$w2 = 5;
	
	$pcnt = (int)($total_num / $rows + (($total_num % $rows) == 0 ? 0 : 1));
	$cpag = (int)($start / $rows + 1);
	$wbgn = $cpag - $w1;
	$wend = $cpag + $w2;
	
	if ($wbgn < 1) {
		$wbgn = 1;
		$wend = $wbgn + $wsiz;
		if ($wend > $pcnt + 1) {
			$wend = $pcnt + 1;
		}
	}
	if ($wend > $pcnt + 1 ) {
		$wend = $pcnt + 1;
		$wbgn = $wend - $wsiz;
		if ($wbgn < 1) {
			$wbgn = 1;
		}
	}
	
	if ($cpag > 1) {
?>
					<a href="./search.php?q=<?php echo urlencode($query); ?><?php foreach ($fq as $q) { ?>&fq%5B%5D=<?php echo urlencode($q); ?><?php } ?>&start=<?php echo urlencode(($cpag - 2) * $rows); ?>&rows=<?php echo urlencode($rows); ?>&fl=<?php echo urlencode($fl); ?>&facet=<?php echo urlencode($facet); ?><?php foreach ($facet_field as $ff) { ?>&facet.field%5B%5D=<?php echo urlencode($ff); ?><?php } ?>&facet.mincount=<?php echo urlencode($facet_mincount); ?>">&lt;&lt;</a>&nbsp;
<?php
	}
	
	for ($i = $wbgn; $i < $wend; $i++) {
		if ($cpag == $i) {
?>
						&nbsp;<?php echo htmlspecialchars((string)$i, ENT_NOQUOTES, 'utf-8'); ?>&nbsp;
<?php
		} else {
?>
						&nbsp;<a href="./search.php?q=<?php echo urlencode($query); ?><?php foreach ($fq as $q) { ?>&fq%5B%5D=<?php echo urlencode($q); ?><?php } ?>&start=<?php echo urlencode(($i - 1) * $rows); ?>&rows=<?php echo urlencode($rows); ?>&fl=<?php echo urlencode($fl); ?>&facet=<?php echo urlencode($facet); ?><?php foreach ($facet_field as $ff) { ?>&facet.field%5B%5D=<?php echo urlencode($ff); ?><?php } ?>&facet.mincount=<?php echo urlencode($facet_mincount); ?>"><?php echo htmlspecialchars((string)$i, ENT_NOQUOTES, 'utf-8'); ?></a>&nbsp;
<?php
		}
	}
	
	if ($cpag < $pcnt) {
?>
						&nbsp;<a href="./search.php?q=<?php echo urlencode($query); ?><?php foreach ($fq as $q) { ?>&fq%5B%5D=<?php echo urlencode($q); ?><?php } ?>&start=<?php echo urlencode($cpag * $rows); ?>&rows=<?php echo urlencode($rows); ?>&fl=<?php echo urlencode($fl); ?>&facet=<?php echo urlencode($facet); ?><?php foreach ($facet_field as $ff) { ?>&facet.field%5B%5D=<?php echo urlencode($ff); ?><?php } ?>&facet.mincount=<?php echo urlencode($facet_mincount); ?>">&gt;&gt;</a>&nbsp;
<?php
	}
?>
					</div><!-- end pagenation -->
<?php
}
?>
				</div><!-- end searchresult -->
			</div><!-- end main -->
			
<?php
if ($result) {
	if ($facet === "true") {
?>
			<div id="left"><!-- start left -->
<?php
	foreach ($result->facet_counts as $facet_counts) {
			foreach ($facet_counts as $field => $values) {
?>
				<div id="facet"><!-- start facet -->
					<?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?>
<?php
				foreach ($values as $value => $count) {
					$p = strpos($value, " ");
					$v = ($p !== false) ? '"'.$value.'"' : $value;
?>
						<div id="facet_value"><!-- start facet_value -->
							<a href="./search.php?q=<?php echo urlencode($query); ?><?php foreach ($fq as $q) { ?>&fq%5B%5D=<?php echo urlencode($q); ?><?php } ?>&fq%5B%5D=<?php echo urlencode($field); ?>:<?php echo urlencode($v); ?>&start=<?php echo urlencode($default_start); ?>&rows=<?php echo urlencode($rows); ?>&fl=<?php echo urlencode($fl); ?>&facet=<?php echo urlencode($facet); ?><?php foreach ($facet_field as $ff) { ?>&facet.field%5B%5D=<?php echo urlencode($ff); ?><?php } ?>&facet.mincount=<?php echo urlencode($facet_mincount); ?>"><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></a>&nbsp;(<?php echo htmlspecialchars($count, ENT_NOQUOTES, 'utf-8'); ?>)
						</div><!-- end facet_value -->
<?php
				}
?>
				</div><!-- end facet -->
<?php
			}
		}
?>
			</div><!-- end left -->
<?php
	}
}
?>
			<div id="foot"><!-- start foot -->
				sample
			</div><!-- end foot -->
		</div><!-- end container -->
	</body>
</html>
