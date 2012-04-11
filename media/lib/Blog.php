<?php

class Blog{
	private static $base = "";

	public static function insert($title, $author, $body){
		if(Auth::executed()){
			$c_title = DB::escape($title);
			$c_author = DB::escape($author);
			$c_body = DB::escape($body);

			DB::set("INSERT INTO ".System::getConfig('blogTable')."(title, author, created, changed, body) VALUES ('$c_title', $c_author, NOW(), NOW(), '$c_body')");
		}
	}

	public static function update($id, $body, $title){
		if(Auth::executed()){
			$c_id = DB::escape($id);
			$c_body = DB::escape($body);
			$c_title = DB::escape($title);
			DB::set("UPDATE ".System::getConfig('blogTable')." SET changed = NOW(), body = '$c_body', title = '$c_title' WHERE id = $c_id");
		}
	}

	public static function delete($id){
		if(Auth::executed()){
			$c_id = DB::escape($id);
			DB::set("DELETE FROM ".System::getConfig('blogTable')." WHERE id = $c_id");
		}
	}

	public static function getPosts($offset = 0, $limit = 10, $echo = true){
		$c_offset = DB::escape($offset);
		$c_limit = DB::escape($limit);
		$res = DB::get("SELECT ".System::getConfig('blogTable').".id AS id, title, CONCAT(SUBSTRING(firstname,1,1),'. ',lastname) AS name, body FROM ".System::getConfig('blogTable')." INNER JOIN ".System::getConfig('userTable')." ON ".System::getConfig('blogTable').".author = ".System::getConfig('userTable').".id LIMIT $c_limit OFFSET $c_offset");
		$dlen = count($res);

		if($echo == true){
			$output = "";
			for($i=0;$i<$dlen;$i++){
				$link = $this->base."?post=".$res[$i]['id'];
				$title = $res[$i]['title'];
				$author = $res[$i]['author'];
				$body = $res[$i]['body'];

				$output .= <<<EOD
					<div class="post">
						<h2><a href="$link">$title</a></h2>
						<small>$author</small>
						$body
						<div class="morewrap"><a href="$link">Les mer</a></div>
					</div>
EOD;
			}
			return $output;
		}else{
			return $res;
		}
	}

	public static function getSingle($id){
		$c_offset = DB::escape($offset);
		$c_limit = DB::escape($limit);
		$res = DB::get("SELECT ".System::getConfig('blogTable').".id AS id, title, CONCAT(SUBSTRING(firstname,1,1),'. ',lastname) AS name, body FROM ".System::getConfig('blogTable')." INNER JOIN ".System::getConfig('userTable')." ON ".System::getConfig('blogTable').".author = ".System::getConfig('userTable').".id LIMIT $c_offset OFFSET $c_limit");
		
		$link = $this->base."?post=".$res['id'];
		$title = $res['title'];
		$author = $res['author'];
		$body = $res['body'];

		return <<<EOD
				<div class="post">
					<h2><a href="$link">$title</a></h2>
					<small>$author</small>
					$body
					<div class="morewrap"><a href="$link">Les mer</a></div>
				</div>
EOD;
	}
}