#!/bin/bash

# HOW TO RUN:

# Have the server running first:
# $ php -S 127.0.0.1:8000 -t public

# List all users:
# $ client/client.sh getallusers
# adapt the code with jq (https://stedolan.github.io/jq/) if you need pretty-printing

# List a user ID:
# $ client/client.sh getuser 2
# adapt the code with jq (https://stedolan.github.io/jq/) if you need pretty-printing

# Add a user:
# $ client/client.sh adduser '{"firstname": "Dennis", "lastname": "Ritchie", "firstparent_id": 4, "secondparent_id": 3}'

# Update a user by ID:
# $ client/client.sh updateuser 5 '{"firstname": "Dennis", "lastname": "Ritchie", "firstparent_id": 5, "secondparent_id": 6}'

# Delete a user by ID:
# $ client/client.sh deleteuser 7

ENV="$1"
[ ! -f "$ENV" ] && ENV= || shift

get_token() {
    curr_dir="$(pwd)"
    dir="$0"
    full_path="$curr_dir/$dir"
    full_dir="$(dirname "$full_path")"
    get_token="${full_dir}/get_token.sh"
    token="$(bash $get_token "$ENV")"
    return
}

create_headers() {
    for ((i=0; i<${#headers[@]}; i++)); do
        headers_str+=" -H '${headers[$i]}'"
    done
    headers_str=${headers_str:1}
}

COMMAND=$1
shift
ID="$1"

headers=()
headers+=("Content-Type: application/json")

headers_str=''
create_headers

URL="http://127.0.0.1:8000/person"

if [[ "$COMMAND" == "getallusers" || "$COMMAND" == "getuser" ]]; then
    [ "$COMMAND" == "getuser" ] && URL+="/$ID"
    curlcmd="curl -X GET $URL"
    eval "$curlcmd"
    echo ""
elif [ "$COMMAND" == "adduser" ]; then
    input="$1"
    tempfile=$(mktemp)
    curlcmd="curl -X POST $headers_str -d '$input' -D $tempfile  $URL"
    eval "$curlcmd"
    echo ""
    status_code=$(head -1 $tempfile | cut -f2- -d' ' | sed 's/\r//')
    [ "$status_code" != "401 Unauthorized" -a "$status_code" != "404 Not Found" ] && echo "$status_code"
    rm $tempfile
elif [ "$COMMAND" == "updateuser" ]; then
    URL+="/$ID"
    shift
    input="$1"
    tempfile=$(mktemp)
    curlcmd="curl -X PUT $headers_str -d '$input' -D $tempfile $URL"
    eval "$curlcmd"
    echo ""
    status_code=$(head -1 $tempfile | cut -f2- -d' ' | sed 's/\r//')
    [ "$status_code" != "401 Unauthorized" -a "$status_code" != "404 Not Found" ] && echo "$status_code"
    rm $tempfile
elif [ "$COMMAND" == "deleteuser" ]; then
    URL+="/$ID"
    tempfile=$(mktemp)
    curlcmd="curl -X DELETE -D $tempfile $URL"
    eval "$curlcmd"
    echo ""
    status_code=$(head -1 $tempfile | cut -f2- -d' ' | sed 's/\r//')
    [ "$status_code" != "401 Unauthorized" -a "$status_code" != "404 Not Found" ] && echo "$status_code"
    rm $tempfile
else
    echo "Command not found"
    echo 'Command = getallusers|getuser|adduser|updateuser|deleteuser'
fi
