"""
HOW TO RUN:

Have the server running first:
$ php -S 127.0.0.1:8000 -t public

List all users:
$ python client/client.py getallusers
# adjust the prints for the output

List a user ID:
$ python client/client.py getuser 2
# adjust the prints for the output

Add a user:
$ python client/client.py adduser '{"firstname": "Dennis", "lastname": "Ritchie", "firstparent_id": 4, "secondparent_id": 3}'

Update a user by ID:
$ python client/client.py updateuser 5 '{"firstname": "Dennis", "lastname": "Ritchie", "firstparent_id": 5, "secondparent_id": 6}

Delete a user by ID:
$ python client/client.py deleteuser 7
"""

import requests
import sys
import json

url = 'http://127.0.0.1:8000/person'

def getAllUsers():
    # resp = requests.post(url, data=params.data, headers=params.headers, timeout=5)
    resp = requests.get(url, timeout=5)
    if (not(resp.status_code >= 200 and resp.status_code < 300)):
        print(f'Error: {resp.status_code} ({resp.reason})')
        sys.exit(1)
        # raise ConnectionError
    jsonpy = resp.json()
    # print_python_object(jsonpy) # print this for python object
    # print_json(jsonpy) # print this for json format

    table = prepare_table(jsonpy)
    # print_table(table) # print this for 2 dimensional array

    print_csv(table) # print below for comma-sepparated values

def getUser(id):
    global url
    url += f'/{id}'
    resp = requests.get(url, timeout=5)
    if (not(resp.status_code >= 200 and resp.status_code < 300)):
        print(f'Error: {resp.status_code} ({resp.reason})')
        sys.exit(1)
        # raise ConnectionError
    jsonpy = resp.json()
    # print_python_object(jsonpy) # print this for python object
    # print_json(jsonpy) # print this for json format

    table = prepare_table(jsonpy)
    # print_table(table) # print this for 2 dimensional array

    print_csv(table) # print below for comma-sepparated values

def addUser():
    headers = {
        'Content-Type': 'application/json;charset=utf-8',
    }
    data = sys.argv[2]
    resp = requests.post(url, data=data, headers=headers, timeout=5)
    if (not(resp.status_code >= 200 and resp.status_code < 300)):
        print(f'Error: {resp.status_code} ({resp.reason})')
        sys.exit(1)
        # raise ConnectionError
    print(f'Status code: {resp.status_code} ({resp.reason})')
    jsonpy = resp.json()
    print(f'ID: {jsonpy["id"]}')

def updateUser(id):
    global url
    url += f'/{id}'
    headers = {
        'Content-Type': 'application/json;charset=utf-8',
    }
    data = sys.argv[3]
    resp = requests.put(url, data=data, headers=headers, timeout=5)
    if (not(resp.status_code >= 200 and resp.status_code < 300)):
        print(f'Error: {resp.status_code} ({resp.reason})')
        sys.exit(1)
        # raise ConnectionError
    print(f'Status code: {resp.status_code} ({resp.reason})')
    jsonpy = resp.json()
    print(f'Rows: {jsonpy["rows"]}')

def deleteUser(id):
    global url
    url += f'/{id}'
    resp = requests.delete(url, timeout=5)
    if (not(resp.status_code >= 200 and resp.status_code < 300)):
        print(f'Error: {resp.status_code} ({resp.reason})')
        sys.exit(1)
        # raise ConnectionError
    print(f'Status code: {resp.status_code} ({resp.reason})')
    jsonpy = resp.json()
    print(f'Rows: {jsonpy["rows"]}')

def print_python_object(jsonpy):
    print(jsonpy)

def print_json(jsonpy):
    print(json.dumps(jsonpy))

def prepare_table(jsonpy):
    headerObj = jsonpy[0]
    header = []
    for item in headerObj:
        header.append(item)

    table = [[*header]]
    for row in jsonpy:
        line = []
        for item in header:
            line.append(row[item])
        table.append(line)
    return table

def print_table(table):
    print(table)

def print_csv(table):
    for row in table:
        print(','.join([str(item) for item in row]))


# getallusers|getuser|adduser|updateuser|deleteuser
if __name__ == '__main__':
    if len(sys.argv) < 2:
        print('No command passed')
        print('command = getallusers|getuser|adduser|updateuser|deleteuser')
        sys.exit(1)

    command = sys.argv[1]
    id = sys.argv[2] if len(sys.argv) > 2 else ''
    if command == 'getallusers':
        getAllUsers()
    elif command == 'getuser':
        getUser(id)
    elif command == 'adduser':
        addUser()
    elif command == 'updateuser':
        updateUser(id)
    elif command == 'deleteuser':
        deleteUser(id)
    else:
        print('Command not found')
        sys.exit(1)
