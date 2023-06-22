<?php namespace App\Models\Telefonia;

use DB;

class RamalRepository
{
    private $ramal;
    private $telefoniaService;

	
    public function __construct(Ramal $ramal,TelefoniaService $telefoniaService){
        $this->ramal = $ramal;
        $this->telefoniaService =$telefoniaService;
    }

    public function listarTodos($paginas = 25){
        return  $this->ramal->where('status',1)->sortable()->orderBy("nro_ramal", "DESC")->paginate($paginas);
    }

	public function listarSetores($id){
		if($id != null){
			$ramal = DB::connection('mysql_voip')->select("SELECT puxargroup from ramal where nro_ramal = $id");

			//dd($ramal);
			$setor = "";
			$final = "";
			$setornull = "";
			if($ramal != null){				
				foreach($ramal as $key => $value){
					$setor = (array)$value;	
					foreach ($setor as $v_key => $v_value) {	
						$setornull = $v_value;
					}
					if($setornull != null){
						foreach ($value as $v_key => $v_value) {
							$puxar = $v_value;
							$final .= $puxar;
							$final = rtrim($final, ",");
						}
						$setores = DB::connection('mysql_voip')->select("SELECT id_setor, nome FROM combosetores where id_setor not in ($final) ORDER BY nome");
						return $setores;						
					}else{
						$setores = DB::connection('mysql_voip')->select("SELECT id_setor, nome FROM combosetores ORDER BY nome");
						return $setores;
					}				
				}	
			}			
		}else{
			$setores = DB::connection('mysql_voip')->select("SELECT id_setor, nome FROM combosetores ORDER BY nome");
			return $setores;			
		}
	}

	public function listarSetoresHabilitados($id){
		if($id != null){
			$ramal = DB::connection('mysql_voip')->select("SELECT puxargroup from ramal where nro_ramal = $id");
			$setor = "";
			$final = "";
			$setornull = "";
			if($ramal != null){
				foreach($ramal as $key => $value){
					$setor = (array) $value;
					foreach ($setor as $v_key => $v_value) {	
						$setornull = $v_value;
					}
					if($setornull != null){		
						foreach ($value as $v_key => $v_value) {
							$puxar = $v_value;
							$final .= $puxar;
							$final = rtrim($final, ",");						
						}
						$setoreshabilitados = DB::connection('mysql_voip')->select("SELECT id_setor, nome FROM combosetores where id_setor in ($final) ORDER BY nome");
						return $setoreshabilitados;						
					}else{
						$setoreshabilitados = null;					
						return $setoreshabilitados;
					}
				}	
			}	
		}
	}
	
    public function pesquisa($busca,$paginas=25)
    {
        if (is_numeric($busca)){
        return $this->ramal->where("nro_ramal", '=', $busca)->where('status',1)
            ->sortable('nro_ramal')->paginate($paginas);

        }else{
            $setores = DB::connection('mysql_voip')->table('setor')->where('nome','LIKE','%'.$busca.'%')->pluck('id_setor');
            $locals = DB::connection('mysql_voip')->table('local')->where('nome','LIKE','%'.$busca.'%')->pluck('id_local');
            $nomes = DB::connection('mysql_voip')->table('users_ramais')->where('nome','LIKE','%'.$busca.'%')->pluck('ramalvinculado');

            return $this->ramal->whereIn("id_setor", $setores)
                    ->where('status',1)
                ->orWhereIn("id_local", $locals) ->orWhereIn("nro_ramal", $nomes)
                ->sortable('nro_ramal')->paginate($paginas);
       }

    }

    public function voipPermissoes(){
        return  $voipPermissoes =[
            "Todos"=>0,
            //"A Cobrar"=>1,
            "Celular Local"	=>11,
            "Celular Interurbano"=>12,
            "Fixo Local"=>13,
            "Fixo Interurbano"=>14,
        ];
    }

    public function findOrFail($id){
        return $this->ramal->findOrFail($id);
    }

    public function store($dados)
    {   
		$puxar = "";
		$final = "";
		if(isset($dados["puxargroup"])){
			if($dados["puxargroup"] != null){
				foreach($dados["puxargroup"] as $key => $value){
					$value = (array) $value;
					if ($value != '#'){
						foreach ($value as $v_key => $v_value) {
							if ($v_value != '#'){
								$puxar = $v_value.",";
								$final .= $puxar;
							}	
						}
					}
				}
				$final = str_replace('"','',$final);	
				$final = str_replace('Arraste aqui setores aptos a puxar,','',$final);					
				$dados["puxargroup"] = $final;	
			}
	  }

       $ramal =  $this->ramal->create($dados);
       $this->atualizaAsterisk();	  

       return $ramal;
    }


    public function update($dados)
    {	
		if(!is_array($dados["puxargroup"])){	
			$ramal = $dados["nro_ramal"];
			$group = DB::connection('mysql_voip')->select("SELECT puxargroup from ramal where nro_ramal = $ramal");
			$buscar = '';
			$habilitados = '';
			if($group != null){
				foreach($group as $key => $value){
					$value = (array) $value;
					foreach ($value as $v_key => $v_value) {
						if ($v_value != '#'){
							$buscar = $v_value;
							if(in_array($buscar, array($habilitados))){
								
							}else{
								$habilitados .= $buscar;
							}
						}
					}
				}			
			}

			$dados["puxargroup"] = null;
			$this->ramal = $dados;
			$ramal = $this->ramal->save();
			$this->atualizaAsterisk();
			return $ramal;	
		}
		
		//$group = DB::connection('mysql_voip')->select("SELECT puxargroup from ramal where nro_ramal = $ramal");
		$puxar = '';
		$final = '';
		if(is_array($dados["puxargroup"])){	
			foreach($dados["puxargroup"] as $key => $value){
				$value = (array) $value;
				foreach ($value as $v_key => $v_value) {
					$puxar = $v_value.",";
					$final .= $puxar;							
				}
			}	
			$final = str_replace('"','',$final);	
			$final = str_replace('Arraste aqui setores aptos a puxar,','',$final);
			//$grupo_final .= $habilitados;
			//$grupo_final .= $final;
			
			$dados["puxargroup"] = $final;
			//echo "<br>Habilitados: ".$grupo_final;
			//echo "<br>Final: ".$final;			
		
			//exit;
			
			$this->ramal = $dados;
			$ramal = $this->ramal->save();
			$this->atualizaAsterisk();		
			return $ramal;			
        }
		

		
    }

    public function atualizaAsterisk(){
        //Comando
        $exec    = "sudo php /var/www/html/sisvoipdev/write/sip.php";
        //Envia Comando
      //  $comando = "sudo ssh -l central -i /home/chaves_ssh/asterisk -p 8836 -tt voip.prefa.br '" . $exec . "'";
        $comando = "sudo /usr/bin/ssh -l central -i /home/chaves_ssh/asterisk -p 8836 -tt voip.prefa.br '" . $exec . "'" ;
      //  $comando = exec($send);
        //   $send = exec("ssh -l central -i /home/chaves_ssh/asterisk -p 8836 -tt voip.prefa.br '" . $exec . "'" );
        shell_exec($comando);
        return true;

    }

    public function tratarPermissoes($dados,$nro_ramal){
        $listaForm = [];
        foreach ($dados as $key => $value){
            if (strpos($key, 'TELEFONIA') !== false) {
                $todos = explode('|',$key);
                array_push($listaForm,(int)$todos[1]);
            }

        }
        $valor = ' ';
        if (in_array(0,$listaForm)){
            $valor .= " 0,";
        }else{
            foreach ($listaForm as $key => $value){
                $valor .=$value.", ";

            }
        }
        if ($valor==" "){
            $valor = " 0,";
        }
        if($this->ramal->where('nro_ramal',$nro_ramal)->update(['permissoes'=>rtrim($valor)])){
                return true;
        }else{
            return false;
        }
    }



    public function sugestao_novo_ramal($q){
        $numeros = [];
        for($i = 200; $i <= 999; $i++)
        {
            if(strlen($q) == 1)
            {
                $nro = substr($i,0,1);
            }
            elseif(strlen($q) == 2)
            {
                $nro = substr($i,0,2);
            }
            elseif(strlen($q) == 3)
            {
                $nro = substr($i,0,3);
            }
            if($nro == $q)
            {
                $consulta = $this->ramal->where("nro_ramal", '=', $i)->pluck('nro_ramal')->first();

                if($consulta == 0)
                {

                    array_push($numeros,$i);
                   // $numeros[]= $i;
                }
            }

        }
        return $numeros;
    }


    public function listaDdrSelect($numero_ddr,$novo=false){
        $buscas = DB::connection('mysql_voip')->select("SELECT * FROM numeros_DDR ORDER BY inicial");
        $select ="<select class=\"form-control\" name=\"numero_ddr\">";
       if ($novo){
           $select .= "<option value=NULL >SEM RAMAL</option>";
       }else{
        if(is_null($numero_ddr) || $numero_ddr=='NULL' ){
            $select .= "<option value=NULL >SEM RAMAL</option>";
        }else{
            $select .= "<option value=NULL >SEM RAMAL</option>";
            $select .= "<option value=".$numero_ddr." >".$numero_ddr."</option>";

        }

       }

        foreach ($buscas as $busca){
            for ($i = $busca->inicial; $i <= $busca->final; $i++) {
                if (@$numero_ddr == $i) {
                    $selected_ddr = "selected = 'selected'";
                } else {
                    $selected_ddr = NULL;
                }
                $consulta_existencia = DB::connection('mysql_voip')->select("SELECT * FROM ramal WHERE numero_ddr = '$i' AND numero_ddr != '$numero_ddr'");
                $buscaX = str_replace("3254", "", $i);
                $consulta_queues =  DB::connection('mysql_voip')->table('queues')->where('DDR','LIKE','%'.$buscaX.'%')->pluck('DDR')->first();
                if (empty($consulta_existencia) && empty($consulta_queues)){
                    $select .= "<option value=".$i." $selected_ddr>".$i."</option>";

                }

            }
        }
        $select .= "</select>";
        return $select;
    }

    public function ramalsOnline(){
        $ramals = $this->telefoniaService->buscaRamalsOnline();
       // dd($ramals);
        return $ramals;
    }
}
