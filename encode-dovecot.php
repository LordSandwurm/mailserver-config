<?php
class encodeDovecot 
{
	# encode with existing salt from password
	public static function encode_with_existing_salt($mode,$password,$db_password) 
	{
		# Return 1 if variable are empty. Return 2 if hashmode is not suported.
		if (empty($password))
			return 1;
		if (empty($db_password))
			return 1;
		if (empty($mode))
			return 1;
		if ($mode !== 'sha512' && $mode !== 'sha256')
			return 2;
			
		switch ($mode){
			case 'sha512':
				#get salt from DB password
				$_salt = substr(base64_decode($db_password), 64);
				break;
			case 'sha256':
				#get salt from DB password
				$_salt = substr(base64_decode($db_password), 32);				
				break;
		}
			

		#hash password from input with salt from database
		$hash = base64_encode(hash($mode, $password . $_salt, TRUE).$_salt);
		
		return $hash;
	}
	
	# encode withe new generated salt
	public static function encode_with_new_salt($mode,$password)
	{
		# check if variable are empty and return 1 if this is so
		if (empty($password))
			return 1;
		if (empty($mode))
			return 1;
		if ($mode !== 'sha512' && $mode !== 'sha256')
			return 2;		
		
		# generate random salt
		$newsalt = self::randomString(8);

		$hash = base64_encode(hash($mode, $password . $newsalt, TRUE).$newsalt);
		
		return $hash;
	}
	
	private static function randomString($length) 
	{
		$str = "";
		$characters = array_merge(range('!','~'));
		$max = count($characters) - 1;
		for ($i = 0; $i < $length; $i++) 
		{
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}
		return $str;
	}
}




?>