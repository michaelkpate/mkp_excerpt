	function mkp_excerpt($atts) {
		global $thisarticle;

		extract(lAtts(array(
			'linkwraptag' => 'p',
			'linkclass' => '',
			'linklabel' => '',
			'linktext' => 'Keep Reading...',
			'excerptwraptag' => '',
			'length' => 0,
			'words' => 0,
			'sentences' => 0,
			'paragraphs' => 0,
			'skiplength' => 0,
			'skipwords' => 0,
			'skipsentences' => 0,
			'skipparagraphs' => 0,
			'ending' => '...',
			'showlinkwithbody' => 0,
			'showlinkwithexcerpt' => 1,
			'overrideexcerpt' => 0,
			'striptags' => 0,
			'stripbreakstabs' => 0,
			'wrapreadmore' => 0,
			'excerpt' => 'excerpt',
			'body' => 'body',
		),$atts));

		$excerpt = trim($thisarticle[$excerpt]);
		$body = trim($thisarticle[$body]);

		// if the article has an excerpt, show it
		if ($excerpt != '' && !$overrideexcerpt) {
			$autoex = ($excerptwraptag) ? tag(parse($excerpt),$excerptwraptag).n : parse($excerpt).n;
			$autoex.= ($showlinkwithexcerpt) ? doTag($linklabel . permlink(array(), $linktext),$linkwraptag,$linkclass) : "";
			return $autoex;
		}

		$body = ($striptags) ? strip_tags($body) : $body;

		$body = ($stripbreakstabs) ? ereg_replace("\n|\r|\r\n|\n\r|\t", " ", preg_replace('/\s\s+/', ' ', trim($body))) : $body;
		$charcount = strlen($body);
		$wordcount = count(explode(" ", $body));
		$sencount = count(explode(".", $body));
		$pghcount = count(explode("</p>", $body));

		if (!$length && !$words && !$sentences && !$paragraphs) $length = 150;

		$doex = 0;
		$doex = ($length) ? ($length < $charcount) ? 1 : 0 : $doex;
		$doex = ($words) ? ($words < $wordcount) ? 1 : 0 : $doex;
		$doex = ($sentences) ? ($sentences < $sencount) ? 1 : 0 : $doex;
		$doex = ($paragraphs) ? ($paragraphs < $pghcount) ? 1 : 0 : $doex;

		if ($doex) {
			if ($length) {
				$exa = explode(" ", substr($body, $skiplength, $length));
				if (count($exa) > 1) array_pop($exa);
				$ex = implode(" ",$exa);
			} else if ($words) {
				$allwords = explode(" ", $body);
				$awa = array_slice($allwords, $skipwords, $words);
				$ex = implode(" ",$awa);
			} else if ($sentences) {
				$allsens = explode(".", $body);
				$asa = array_slice($allsens, $skipsentences, $sentences);
				$ex = implode(".",$asa).".";
				$ending = "";
			} else if ($paragraphs) {
				$allpghs = explode("</p>", $body);
				$awp = array_slice($allpghs, $skipparagraphs, $paragraphs);
				$ex = implode("</p>",$awp);
				$ending = "";
			}
			$ex.= $ending.rssAutoCloseTags($ex);

			// find the last closing tag
			preg_match_all("/<\/(([A-Z]|[a-z]).*)(( )|(>))/isU",$ex,$closetags);
			$lasttag = end($closetags[0]);

			// add the read more text either inside the last closing tag or outside
			if ($wrapreadmore && $lasttag) {
				$exrm = ($showlinkwithexcerpt) ? doTag($linklabel . permlink(array(), $linktext),$linkwraptag,$linkclass) : '';
				$ex = substr($ex, 0, -(strlen($lasttag))) . $exrm . $lasttag;
			} else {
				$ex.= ($showlinkwithexcerpt) ? n . doTag($linklabel . permlink(array(), $linktext),$linkwraptag,$linkclass) : '';
			}

		} else {
			$ex = parse($body);
			$ex.= ($showlinkwithbody) ? doTag($linklabel . permlink(array(), $linktext),$linkwraptag,$linkclass) : '';
		}

		return $ex;
	}

// ----------------------------------------------------

	function rssAutoCloseTags($string) {
		// automatically close HTML-Tags
		// (usefull e.g. if you want to extract part of a blog entry or news as preview/teaser)
		// coded by Constantin Gross <connum at googlemail dot com> / 3rd of June, 2006
		// feel free to leave comments or to improve this function!
		// Updates by Rob Sable <www.wilshireone.com> / 20th of August, 2006

		$donotclose=array('br','img','input'); //Tags that are not to be closed

		//prepare vars and arrays
		$tagstoclose='';
		$tags=array();

		//put all opened tags into an array
		preg_match_all("/<(([A-Z]|[a-z]).*)(( )|(>))/isU",$string,$result);
		$openedtags=$result[1];
		$openedtags=array_reverse($openedtags); //this is just done so that the order of the closed tags in the end will be better

		//put all closed tags into an array
		preg_match_all("/<\/(([A-Z]|[a-z]).*)(( )|(>))/isU",$string,$result2);
		$closedtags=$result2[1];

		//look up which tags still have to be closed and put them in an array
		for ($i=0;$i<count($openedtags);$i++) {
			 if (in_array($openedtags[$i],$closedtags)) { unset($closedtags[array_search($openedtags[$i],$closedtags)]); }
					 else array_push($tags, $openedtags[$i]);
		}
		//$tags=array_reverse($tags); //now this reversion is done again for a better order of close-tags

		//prepare the close-tags for output
		for($x=0;$x<count($tags);$x++) {
		$add=strtolower(trim($tags[$x]));
		if(!in_array($add,$donotclose)) $tagstoclose.='</'.$add.'>';
		}

		//and finally
		return $tagstoclose;
	}
