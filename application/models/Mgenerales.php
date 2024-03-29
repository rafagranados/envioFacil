<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* 
*/
class mgenerales extends CI_Model{
	
	function __construct(){
		parent::__construct();
		$this->load->database();
	}

    function getListaDepartamento(){

        $this->db->select('id_departamento ,nombre');
        $this->db->where("estado_id",1);
        $query = $this->db->get('departamento');

        if ($query->num_rows()>0) {
			return $query->result();
		}
		
		return false;
    }

    function getListaTipoDocumento(){

        $this->db->select('id_tipo_documento_identidad  ,tipo_documento');
        $this->db->where("estado_id",1);
        $query = $this->db->get('tipo_documento_identidad');

        if ($query->num_rows()>0) {
			return $query->result();
		}
		
		return false;
    }

    function getEstado(){

        $this->db->select('id_estado,estado');
        $query = $this->db->get('estado');

        if ($query->num_rows()>0) {
			return $query->result();
		}
		
		return false;
    }

    function getCountDespachados() {
        $this->db->select('COUNT(estado_id) as count');
        $this->db->where("cuenta_id",$this->session->userdata('id_cuenta'));
        $this->db->where("estado_id", 10);
        $query = $this->db->get('pedido');

        if ($query->num_rows()>0) {
			// return $query->result();
            $result = $query->row();
            return $result->count;  
		}
		
		return false;
    }

    function getCountNoDespachados() {
        $this->db->select('COUNT(estado_id) as count');
        $this->db->where("cuenta_id",$this->session->userdata('id_cuenta'));
        $this->db->where("estado_id", self::IMPRESO);
        $query = $this->db->get('pedido');

        if ($query->num_rows()>0) {
			// return $query->result();
            $result = $query->row();
            return $result->count;  
		}
		
		return false;
    }

    function InsertarElemento($tabla,$data){

        if(!empty($tabla) && !empty($data) && is_array($data)){

            try {
                
                $this->db->insert($tabla,$data);
                $error = $this->db->error();
    
                if($error['code'] == 0){
        
                return  $this->db->insert_id() ? $this->db->insert_id()  : true;

                }else{
                
                $this->db->insert('log_db',['descripcion' => json_encode($this->db->error()),'adicional' => $tabla]);
                return  false; 
                }
            } catch (Exception $e) {
                return  false; 
            }
        }

        return false;
    }

    function cargarSelect($valor_filtro,$tabla,$value,$display){

        $this->db->select($value.' as value,'.$display.' as display');
        $this->db->where("departamento_id ",$valor_filtro );
        $query = $this->db->get($tabla);

        if ($query->num_rows()>0) {
			return $query->result();
		}
		
		return false;
    }

    function getDataSesion(){

        $this->db->select('u.email,u.avatar,u.direccion,u.celular,m.codigo_transportadora,m.nombre as municipio, d.id_departamento,d.nombre as departamento,u.numero_documento');
		$this->db->from('usuario u');
		$this->db->join('cuenta c', 'c.id_cuenta  = u.cuenta_id');
        $this->db->join('municipio m', 'm.id_municipio  = u.municipio_id','left');
		$this->db->join('departamento d', 'd.codigo  = m.departamento_id','left');
        $this->db->where("u.id_usuario",$this->session->userdata('id_usuario'));
        $query = $this->db->get();
        $dataUser = $query->row();
    
        $data = [
            'id_usuario'        => $this->session->userdata('id_usuario'),
            'id_cuenta'         => $this->session->userdata('id_cuenta'),
            'nombre_user'       => $this->session->userdata('nombre').' '. $this->session->userdata('apellidos'),
            'numero_documento'  => $dataUser->numero_documento,
            'direccion'         => $dataUser->direccion,
            'celular'           => $dataUser->celular,
            'email_user'        => $dataUser->email ?? 'Correo no registrado',
            'avatar'            => $dataUser->avatar ?? 'default_avatar.png',
            'codigo_transportadora'  =>$dataUser->codigo_transportadora,
            'municipio'             =>$dataUser->municipio, 
            'id_departamento'       =>$dataUser->id_departamento,
            'departamento'          => $dataUser->departamento

        ];

        return $data;
    }

    function cambiarEstadoElemento($tabla,$valor,$id_tabla,$campo){

        $response = new stdClass();
		$response->status  = false;
		$response->message = 'Se presento un error al actulizar el estado';
		
		$this->db->where($campo, $id_tabla);
		$this->db->update($tabla,['estado_id' =>$valor]);
		
		if ($this->db->affected_rows() > 0) {
			$response->status  = true;
			$response->message = 'Estado actulizado de forma exitosa.';
			return $response;
		}elseif($this->db->affected_rows() == 0){
			$response->status  = true;
			$response->message = 'No se detectó ningún cambio.';
			return $response;
		}

		return $response;

    }

    function getdataTablaUsuarios(){
        
        $this->db->select("DATE(u.fecha_registro) as fecha_registro , UPPER(CONCAT(u.nombre,' ',u.apellido)) as nombre_completo,u.email,u.celular,e.estado,u.id_usuario,e.id_estado");
		$this->db->from('usuario u');
		$this->db->join('cuenta c', 'c.id_cuenta  = u.cuenta_id');
        $this->db->join('estado e', 'e.id_estado  = u.estado_id');
        $this->db->where("u.id_usuario <> ",$this->session->userdata('id_usuario'));
        $query = $this->db->get();

        if ($query->num_rows()>0) {
			return $query->result();
		}

		return false;
    }

    function getDepartamentos() {
		$this->db->select('codigo, nombre');
		$this->db->from('departamento');
        $this->db->order_by('nombre');
		$query = $this->db->get();

		if ($query->num_rows()>0) {
			return $query->result();
		}
		
		return false;
	}

    function getMunicipios($id_dpto) {
		$this->db->select('id_municipio,codigo_transportadora, nombre');
		$this->db->from('municipio m');
		$this->db->where("m.departamento_id = '".$id_dpto."'");
        $this->db->order_by('nombre');
		$query = $this->db->get();

		if ($query->num_rows()>0) {
			return $query->result();
		}
		
		return false;
	}

    function getGuiasSinLiquidar(){

        $this->db->select("
            c.id_cuenta,
            UPPER(concat(u.nombre,' ',u.apellido)) as usuario,
            count(1) as total_guias, 
            CONCAT('$',REPLACE(FORMAT(sum(p.valor_declarado - p.valor_comision - p.valor_flete),0),',','.')) as valor_pagar"
        );

		$this->db->from('pedido p');
        $this->db->join('cuenta c', 'c.id_cuenta  = p.cuenta_id');
        $this->db->join('usuario u', 'u.cuenta_id = c.id_cuenta');
		$this->db->where("p.estado_liquidacion",self::ESTADO_PENDIENTE);
        $this->db->where("p.estado_id",self::ESTADO_ENTREGADO);
        $this->db->group_by("c.id_cuenta");
		$query = $this->db->get();

		if ($query->num_rows()>0) {
			return $query->result();
		}
		
		return false;

    }

    function getValorGuiasEntregadas(){

        $this->db->select("REPLACE(FORMAT(SUM(valor_declarado - (valor_comision + valor_flete)),0),',','.') as valor");

		$this->db->from('pedido p');
        $this->db->join('cuenta c', 'c.id_cuenta  = p.cuenta_id');
        $this->db->where("p.cuenta_id",$this->session->userdata('id_cuenta'));
		$this->db->where("p.estado_liquidacion",self::ESTADO_PENDIENTE);
        $this->db->where("p.estado_id",self::ESTADO_ENTREGADO);        

		$query = $this->db->get();

		if ($query->num_rows()>0) {
			return $query->row();
		}
		
		return false;

    }
}