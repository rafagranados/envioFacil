<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* 
*/
class musuario extends CI_Model{
	
	function __construct(){
		parent::__construct();
		$this->load->database();
	}

    function validarEmailExiste($email){

        $this->db->where("email", $email);
		$query = $this->db->get("usuario");

		if ($query->num_rows()>0) {
			return $query->row();
		}
		else{
			return false;
		}
    }

    function login($email, $password){
		$this->db->where("email", $email);
		$this->db->where("clave", $password);
		$query = $this->db->get("usuario");
		if ($query->num_rows()>0) {
			return $query->row();
		}
		else{
			return false;
		}
	}

    function UserCountEmpresa($id_usuario){

		$this->db->select('1');
		$this->db->from('empresa e');
		$this->db->join('cuenta c', 'c.id_cuenta  = e.cuenta_id');
		$this->db->join('usuario u', 'u.cuenta_id  = c.id_cuenta');
        $this->db->where("u.id_usuario",$id_usuario);
        $query = $this->db->get();
        return $query->num_rows();
    }

	function CambiarEstadoUsuario($id_usuario,$id_estado){

		$response = new stdClass();
		$response->status  = false;
		$response->message = 'Se presento un error al actulizar los datos';
		
		$this->db->where('id_usuario', $id_usuario);
		$this->db->update('usuario',['estado_id' =>$id_estado]);
		
		if ($this->db->affected_rows() > 0) {
			$response->status  = true;
			$response->message = 'Estado actualizado de forma exitosa.';
			return $response;
		}elseif($this->db->affected_rows() == 0){
			$response->status  = true;
			$response->message = 'No se detecto ningún cambio.';
			return $response;
		}

		return $response;
	}
}