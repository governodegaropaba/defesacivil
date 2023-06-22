import requests
import psycopg2
import json
#import os
import csv
from csv import reader
#import sys
import datetime
from requests.api import head

pasta = "/sites/defesacivil/public/csv/"
### REALIZA LOGIN NO CEMADEN ###
token_url = 'http://sgaa.cemaden.gov.br/SGAA/rest/controle-token/tokens'
login = {'email': 'defesacivil@garopaba.sc.gov.br', 'password': 'zSd720!nBa4@'}
response = requests.post(token_url, json=login)
content = response.json()
token = content['token']

### DEFINE A DATA CORRENTE PARA BUSCAR DADOS ###
currentDateTime = datetime.datetime.now()
date = currentDateTime.date()
ano = date.strftime("%Y")
mes = date.strftime("%m")
dia = date.strftime("%d")
ini = ano+mes+dia+'0000'
fim = ano+mes+dia+'2359'

#ini = '202302241020'
#fim = '202302271020'


### BUSCA DADOS DA GAMBOA ###
sws_url='http://sws.cemaden.gov.br/PED/rest/pcds/dados_pcd?codigo=420570401A&fim='+fim+'&inicio='+ini+'&rede=11'
#print(f'URL: {sws_url}')
params = dict(rede=11, uf='SC')
r = requests.get(sws_url, params=params, headers={'token': token})
data = r.text

### BUSCA DADOS DO CAMPO DUNA ###
sws_url='http://sws.cemaden.gov.br/PED/rest/pcds/dados_pcd?codigo=420570402A&fim='+fim+'&inicio='+ini+'&rede=11'
params = dict(rede=11, uf='SC')
r = requests.get(sws_url, params=params, headers={'token': token})
data = data + (r.text)

### BUSCA DADOS DA AREIAS DE PALHOCINHA ###
sws_url='http://sws.cemaden.gov.br/PED/rest/pcds/dados_pcd?codigo=420570403A&fim='+fim+'&inicio='+ini+'&rede=11'
params = dict(rede=11, uf='SC')
r = requests.get(sws_url, params=params, headers={'token': token})
data = data + (r.text)

### FASE 1 - IMPORTA DADOS VINDOS DO CEMADEN PARA CSV ###
with open(pasta+"fase1.csv", "w") as f:
    #writer = csv.writer(f)
    f.write(data)

text = open(pasta+"fase1.csv", "r")

text = ''.join([i for i in text]) \
    .replace(";0000;", ";0000")

### FASE 2 - TRATA ARQUIVO VINDO DA FASE 1 ###
x = open(pasta+"fase2.csv","w")
x.writelines(text)
text = ''.join([i for i in text]) \
    .replace(";0004;", ";0004")
x = open(pasta+"fase2.csv","w")
x.writelines(text)

### SUBSTITUI OBS PARA ELIMINAR NO PRÓXIMO PASSO ###
text = ''.join([i for i in text]) \
    .replace("OBS.: PCD com horario UTC!", "9999999999;Estrada Geral Gamboa;GAROPABA;SC;-27.953;-48.629;2023-01-17 21:00:00;intensidade_precipitacao;0.0;0004")
x = open(pasta+"fase2.csv","w")
x.writelines(text)

### SUBSTITUI CABECALHO PARA ELIMINAR NO PRÓXIMO PASSO ###
text = ''.join([i for i in text]) \
    .replace("cod.estacao;nome;municipio;uf;latitude;longitude;datahora;sensor;valor;qualificacao;offset", "9999999999;Estrada Geral Gamboa;GAROPABA;SC;-27.953;-48.629;2023-01-17 21:00:00;intensidade_precipitacao;0.0;0004")

x = open(pasta+"fase2.csv","w")
x.writelines(text)
x.close()
### FASE 3 - GERA ARQUIVO FINAL ###
try:
    with open(pasta+'fase2.csv', 'r') as fr:
        lines = fr.readlines()

        with open(pasta+'final.csv', 'w') as fw:
            for line in lines:
                ### ELIMINA LINHAS COM VALOR ZERO ###
                if line.find('0.0;') == -1:
                    fw.write(line)
except:
    print("Oops! ocorreu erro")

try:
    with open(pasta+'final.csv', 'r') as fr:
        lines = fr.readlines()
except:
    print("Oops! ocorreu erro aqui")

with open(pasta+'final.csv', 'r') as csv_file:
    linhas = csv_file.read().splitlines()
    csv_reader = reader(linhas, delimiter = ';')
    list_of_rows = list(csv_reader)
    ### ELIMINA 3 ÚLTIMAS LINHAS QUE VEM EM BRANCO ###
    qtde = len(list_of_rows)
    #print('qtde->',qtde)
    file = open(pasta+'final.csv')
    contents = csv.reader(file)

    conn = psycopg2.connect(database="defesacivil",
                            user='informatica', password='gsul@10',
                            host='dbserver.prefa.br', port='5432'
                            )
    conn.autocommit = True
    cursor = conn.cursor()

    for i in range(qtde):
        if(i>=0 and list_of_rows[i][8] != '0.0'):
            #print(list_of_rows[i])
            v_cod_estacao = list_of_rows[i][0]
            #if (v_cod_estacao == '420570401A'):
            #    print(qtde)
            v_nome = list_of_rows[i][1]
            v_municipio = list_of_rows[i][2]
            v_uf = list_of_rows[i][3]
            v_latitude = list_of_rows[i][4]
            v_longitude = list_of_rows[i][5]
            v_datahora = list_of_rows[i][6]
            v_sensor = list_of_rows[i][7]
            v_valor = list_of_rows[i][8]
            v_qualificacao = list_of_rows[i][9]
            seleciona = "SELECT municipio FROM alerta_pcd WHERE cod_estacao = '"+v_cod_estacao+"' and datahora_gmt = '"+v_datahora+"' and valor = "+v_valor+" and sensor = '"+v_sensor+"'"
            #print(seleciona)
            cursor.execute(seleciona)
            resultado = cursor.fetchall()
            #print(len(resultado))
            if len(resultado)==0:  #Verifica se o retorno contém alguma linha
                #print('inserindo')
                cursor.execute("INSERT INTO public.alerta_pcd (cod_estacao, nome, municipio, uf, latitude, longitude, datahora, sensor, valor, qualificacao) VALUES(%s, %s,%s, %s,%s, %s,%s, %s,%s, %s)",(v_cod_estacao, v_nome, v_municipio, v_uf, v_latitude, v_longitude, v_datahora, v_sensor, v_valor, v_qualificacao))
conn.commit()
conn.close()