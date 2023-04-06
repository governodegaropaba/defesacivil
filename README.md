# defesacivil
Medição de pluviômetros para controle de volumes de chuvas.<br>
Tecnologias utilizadas: PHP e Python<br>
Banco de dados: postgre
Origem dos dados: http://sws.cemaden.gov.br
Atualização dos dados é feita instantaneamente de minuto em minuto.
São gerados dados pluviométricos de cada pluviômetro e mostrados em um frontend PHP
Em caso de as chuvas ultrapassarem limites pré-determinados, é enviado email para a defesa civil.(PHPmailer)
Existem gráficos demonstrativos para cada volume de chuvas registrados nas últimas 24h, 36h, 48h, 54h ao clicar nos relógios correspondentes.

Obs.: é necessário obter uma chave de acesso junto ao CEMADEN cadastrando um email e recebendo do CEMADEN uma senha para geração 
de token que será usado na utilização das API´s.


