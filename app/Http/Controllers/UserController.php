<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
	public function index(){
		$users = User::all();

		return response()->json([
    		'code'		=> 200,
    		'status'	=> 'success',
    		'users' 	=> $users
    	]);
	}

	public function show($id){
    	$user = User::find($id);

    	if(is_object($user)){
    		$data = array(
    			'code'		=>	200,
    			'status'	=> 'success',
    			'user'		=> $user
    		);
    	} else {
    		$data = array(
    			'code'		=>	404,
    			'status'	=> 'error',
    			'message'		=> 'El usuario no existe'
    		);
    	}

    	return response()->json($data, $data['code']);
	}
	


    public function store(Request $request){
		//Recoger datos de usuario por POST
		$json = $request->input('json');
		$params = json_decode($json); //objeto
		$params_array = json_decode($json, true); //Array
   	
    	if(!empty($params) && !empty($params_array)){
	    	
	    	//Limpiar datos
	    	$params_array = array_map('trim', $params_array);

			//Validar datos
	    	$validate = Validator::make($params_array, [
	    		'firstname'		=> 'required|alpha',
	    		'lastname'		=> 'required|alpha',
	    		'email'			=> 'required|email|unique:users',
				'password'		=> 'required',
				'phone_number'	=> 'required|alpha_num',
				'default_lang'	=> 'required|alpha',
				'active'		=> 'required|boolean'
	    	]);

	    	if($validate->fails()){ //Validación ha fallado
	    		$data = array(
		    		'status' 	=> 'error',
		    		'code'		=> 404,
		    		'message' 	=> 'El usuario no se ha podido crear',
		    		'errors' 	=> $validate->errors()
		    	);
	    	} else { //Validación pasada correctamente

	    		//Cifrar Contraseña
	    		$pwd = hash('sha256', $params->password);

				//Crear usuario
	    		$user = new User();
	    		$user->firstname = $params_array['firstname'];
	    		$user->lastname = $params_array['lastname'];
	    		$user->email = $params_array['email'];
				$user->password = $pwd;
				$user->phone_number = $params_array['phone_number'];
				$user->default_lang = $params_array['default_lang'];
				$user->active = $params_array['active'];

				//$user->role = 'ROLE_USER';

				//Guardar el usuario
				$user->save();

	    		$data = array(
		    		'status' 	=> 'success',
		    		'code'		=> 200,
		    		'message' 	=> 'El usuario se ha creado correctamente',
		    		'user'		=> $user
		    	);
	    	}
	    } else {
	    	$data = array(
	    		'status' 	=> 'error',
	    		'code'		=> 404,
	    		'message' 	=> 'Los datos enviados no son correctos'
	    	);
	    }

    	//Devolver mensaje
    	return response()->json($data, $data['code']);
    }

    public function login(Request $request){
    	$jwtAuth = new \App\Helpers\jwtAuth();

    	//Recibir datos por POST
    	$json = $request->input('json', null);
    	$params = json_decode($json); //objeto
    	$params_array = json_decode($json, true); //Array

    	//Validar datos
    	$validate = Validator::make($params_array, [
    		'email'		=> 'required|email',
    		'password'	=> 'required'
    	]);

    	if($validate->fails()){ //Validación ha fallado
	    		$signup = array(
		    		'status' 	=> 'error',
		    		'code'		=> 404,
		    		'message' 	=> 'El usuario no se ha podido identificar',
		    		'errors' 	=> $validate->errors()
		    	);
    	} else { //Validación pasada correctamente
			//Cifrar la password
    		$pwd = hash('sha256', $params->password);
 			
 			//Devolver token o datos
    		$signup = $jwtAuth->signup($params->email, $pwd);
    		if(isset($params->gettoken)){
    			$signup = $jwtAuth->signup($params->email, $pwd, true);
    		}
    	}

    	return response()->json($signup, 200);
    }
	
    public function update(Request $request){

    	//Comprobar si el usuario está identificado
    	$token = $request->header('Authorization');
    	$jwtAuth = new \App\Helpers\jwtAuth();
    	$checkToken = $jwtAuth->checkToken($token);
    	//Recoger datos por POST
		$json = $request->input('json', null);
		dd($json);
		$params_array = json_decode($json, true);

    	if($checkToken && !empty($params_array)){    		
    		//Sacar usuario identificado
    		$user = $jwtAuth->checkToken($token, true); 

    		//Validar datos
	    	$validate = Validator::make($params_array, [
				'firstname'		=> 'required|alpha',
	    		'lastname'		=> 'required|alpha',
	    		'email'			=> 'required|email|unique:users, '.$user->sub,
				'password'		=> 'required',
				'phone_number'	=> 'required|alpha_num',
				'default_lang'	=> 'required|alpha',
				'active'		=> 'required|boolean'
	    	]);

    		//Quitar los campos que no quiero actualizar
	    	unset($params_array['id']);
	    	unset($params_array['password']);
	    	unset($params_array['created_at']);
	    	unset($params_array['remember_token']);

    		//Actualizar usuario en la BD
	    	$user_update = User::where('id',$user->sub)->update($params_array);

    		//Devolver array de resultado
    		$data = array(
    			'code' 		=> 200,
    			'status' 	=> 'success',
    			'user'		=> $user,
    			'changes'	=> $params_array
    		);


    	} else {
    		//Mensaje de error
    		$data = array(
    			'code' 		=> 400,
    			'status' 	=> 'error',
    			'message'	=> 'El usuario no está identificado o faltan datos.'
    		);
    	}

    	return response()->json($data, $data['code']);
    }
	/*
    public function upload(Request $request){
    	//Recoger datos
    	$image = $request->file('file0');

    	//Validación de imagen con libreria de Laravel
    	$validate = Validator::make($request->all(), [
    		'file0' 	=> 'required|image|mimes:jpg,jpeg,png,gif'
    	]);

    	//Guardar imagen
    	if(!$image || $validate->fails()){
    		$data = array(
				'code' 		=> 400,
				'status' 	=> 'error',
				'message'	=> 'Error al subir imagen.'
			);
    	}else{
    		$image_name = time().$image->getClientOriginalName();
    		Storage::disk('users')->put($image_name, \File::get($image));

			$data = array(
				'code'		=> 200,
				'status' 	=> 'success',
				'image' 	=> $image_name
			);
    		
    	}

	   	//return response($data, $data['code'])->header('Content-Type', 'text/plain');
	   	return response()->json($data, $data['code']);
    }

    public function getImage($filename){
    	$isset = Storage::disk('users')->exists($filename);
    	if($isset){
    		$file = Storage::disk('users')->get($filename);
    		
    		return new Response($file, 200); //Devuelve un código para usar con <img>
    	} else {
    		$data = array(
				'code' 		=> 404,
				'status' 	=> 'error',
				'message'	=> 'La imagen no existe.'
			);

			return response()->json($data, $data['code']);
    	}
    	
    }*/

}
