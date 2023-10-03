import requests
import psycopg2
import json
#import os
import csv
from csv import reader
import sys
import glob
import system_config

### REALIZA LOGIN NO CEMADEN ###
token_url = system_config.CEMADEN_URL
login = {'email': system_config.CEMADEN_EMAIL, 'password': system_config.CEMADEN_PASS}
response = requests.post(token_url, json=login)
content = response.json()
token = content['token']

data_inicial = input('Entre com a data inicial formato DD/MM/AAAA: ')
dt = data_inicial
datac = dt[:10]
datah = dt[-8:]
dia = datac.split("/")[0]
mes = datac.split("/")[1]
ano = datac.split("/")[2]
ini = ano+mes+dia+'0000'

data_final = input('Entre com a data final formato DD/MM/AAAA: ')
dt = data_final
datac = dt[:10]
datah = dt[-8:]
dia = datac.split("/")[0]
mes = datac.split("/")[1]
ano = datac.split("/")[2]
fim = ano+mes+dia+'2359'

###
### REALIZA A BUSCA PARA CADA PLUVIÔMETRO EXISTENTE NA CIDADE
###

sws_url='http://sws.cemaden.gov.br/PED/rest/pcds/dados_pcd?codigo=420570401A&fim='+fim+'&inicio='+ini+'&rede=11'
#print(f'URL: {sws_url}')
params = dict(rede=11, uf='SC')
r = requests.get(sws_url, params=params, headers={'token': token})
data = r.text

sws_url='http://sws.cemaden.gov.br/PED/rest/pcds/dados_pcd?codigo=420570402A&fim='+fim+'&inicio='+ini+'&rede=11'
params = dict(rede=11, uf='SC')
r = requests.get(sws_url, params=params, headers={'token': token})
data = data + (r.text)

sws_url='http://sws.cemaden.gov.br/PED/rest/pcds/dados_pcd?codigo=420570403A&fim='+fim+'&inicio='+ini+'&rede=11'
params = dict(rede=11, uf='SC')
r = requests.get(sws_url, params=params, headers={'token': token})
data = data + (r.text)
print(data)

with open("arquivos.csv", "w") as f:
    writer = csv.writer(f)
    writer.writerow(data)

text = open("arquivos.csv", "r")
text = ''.join([i for i in text]) \
    .replace(",", "")
text = ''.join([i for i in text]) \
    .replace('"', "")
text = ''.join([i for i in text]) \
    .replace(";0000;", ";0000")

x = open("output.csv","w")
x.writelines(text)
text = ''.join([i for i in text]) \
    .replace(";0004;", ";0004")
x = open("output.csv","w")
x.writelines(text)
text = ''.join([i for i in text]) \
    .replace("OBS.: PCD com horario UTC!", "9999999999;Estrada Geral Gamboa;GAROPABA;SC;-27.953;-48.629;2023-01-17 21:00:00;intensidade_precipitacao;0.0;0004")
x = open("output.csv","w")
x.writelines(text)
text = ''.join([i for i in text]) \
    .replace("cod.estacao;nome;municipio;uf;latitude;longitude;datahora;sensor;valor;qualificacao;offset", "9999999999;Estrada Geral Gamboa;GAROPABA;SC;-27.953;-48.629;2023-01-17 21:00:00;intensidade_precipitacao;0.0;0004")

x = open("output.csv","w")
x.writelines(text)
x.close()
try:
    with open('output.csv', 'r') as fr:
        lines = fr.readlines()

        with open('output1.csv', 'w') as fw:
            for line in lines:
                # find() returns -1
                # if no match found
                if line.find('0.0;') == -1:
                    fw.write(line)
                if line.find('') == -1:
                    fw.write(line)
except:
    print("Oops! someting error")


try:
    with open('output1.csv', 'r') as fr:
        lines = fr.readlines()
except:
    print("Oops! someting error")

with open('output1.csv', 'r') as csv_file:
    linhas = csv_file.read().splitlines()
    csv_reader = reader(linhas, delimiter = ';')
    list_of_rows = list(csv_reader)
    qtde = len(list_of_rows)-3

    #file = open('f:/files/output1.csv')
    #contents = csv.reader(file)

    conn = psycopg2.connect(
        database=system_config.DB_DATABASE,
        user=system_config.DB_USER,
        password=system_config.DB_PASSWORD,
        host=system_config.DB_HOST,
        port=system_config.DB_PORT
    )
    conn.autocommit = True
    cursor = conn.cursor()

    for i in range(qtde):
        if(i>1 and list_of_rows[i][8] != '0.0'):
            #print(list_of_rows[i])
            cod_estacao = list_of_rows[i][0]
            nome = list_of_rows[i][1]
            municipio = list_of_rows[i][2]
            uf = list_of_rows[i][3]
            latitude = list_of_rows[i][4]
            longitude = list_of_rows[i][5]
            dh = list_of_rows[i][6]
            sensor = list_of_rows[i][7]
            valor = list_of_rows[i][8]
            qualificacao = list_of_rows[i][9]
            #insert_records = "INSERT INTO public.alerta_pcd (cod_estacao, nome, municipio, uf, latitude, longitude, datahora, sensor, valor, qualificacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            #sql_insert = "INSERT INTO public.alerta_pcd (cod_estacao, nome, municipio, uf, latitude, longitude, datahora, sensor, valor, qualificacao) VALUES(?,?,?,?,?,?,?,?,?,?)",cod_estacao, nome, municipio, uf, latitude, longitude, dh, sensor, valor, qualificacao)
            #cursor.execute("INSERT INTO public.alerta_pcd (cod_estacao, nome, municipio, uf, latitude, longitude, datahora, sensor, valor, qualificacao) VALUES(?,?,?,?,?,?,?,?,?,?)",cod_estacao, nome, municipio, uf, latitude, longitude, dh, sensor, valor, qualificacao)
            #cursor.executemany(insert_records, contents)
            #select_all = "SELECT * FROM public.alerta_pcd "
            #rows = cursor.execute(select_all).fetchall()
            #for r in rows:
            #    print(r)
            #print(cod_estacao+'-'+nome+'-'+municipio+'-'+uf+'-'+latitude+'-'+longitude+'-'+dh+'-'+sensor+'-'+valor+'-'+qualificacao)
            sql2 = "COPY alerta_pcd FROM 'f:/files/output1.csv' USING DELIMITERS ';' CSV;"
            cursor.execute(sql2)
conn.commit()
conn.close()
