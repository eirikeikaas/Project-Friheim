<?

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('media/lib/Blog.php');

$a = new Auth();
if(isset($_GET['a']) && $_GET['a'] == 'edit'){
	echo getPage('blog_edit');
}else if(isset($_GET['a']) && $_GET['a'] == 'new'){
	echo getPage('blog_new');
}else{
	if(isset($_GET['a']) && $_GET['a'] == 'save'){
		
		// Check the validity of the key
		if(Auth::key($_POST['key']) && $a->isAdmin()){
			$db = &DB::getInstance();

			if($_POST['new'] == "false"){ // OK
				if(Blog::update($_POST['id'], $_POST['body'], $_POST['title'])){
					header("location: ?p=blog&msg=saved");
				}
			}else if($_POST['new'] == "true"){ // OK
			    if(Blog::insert($_POST['title'], $_POST['author'], $_POST['body'])){
					header("location: ?p=blog&msg=saved");
				}
			}
		}
	}
	if($a->isAdmin()){
?>
<form class="block">
	<h2>Blogg<a href="?p=blog&amp;a=new" class="hbtn">Legg til ny artikkel</a></h2>
	<?php if($_GET['msg'] == "saved"){ ?><div class="msg success">Artikkelen ble oppdatert</div><? } ?>
	<table>
		<?php
		
		$posts = Blog::getPosts();

		for($i=0;$i<$dlen;$i++){
			$even = ($i%2)?' class="even"':'';
			?>
			<tr<?=$even?>>
				<td><?=$data[$i]['name']?></td>
				<td><?= (int)$data[$i]['curr'].$data[$i]['unit'] ?> (<?=$data[$i]['total'].$data[$i]['unit']?>) <?=$data[$i]['mp']?>% <?=$data[$i]['tp']?>%</td>
				<td><?=$data[$i]['month']?></td>
				<td><div class="tdwrap"><div class="tag <?=$data[$i]['depts']?>"><?=$data[$i]['dept']?></div><a href="?p=users&amp;a=edit&amp;id=<?=$data[$i]['id']?>" class="editbtn"></a></div></td>
			</tr>
		<? } ?>
	</table>
	<div class="footer">
		<a href="?p=blog&amp;a=new" class="hbtn">Legg til ny artikkel</a>
	</div>
</form>
<? }} ?>