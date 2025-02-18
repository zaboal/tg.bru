#!/usr/bin/bash env

mapfile -t a0 < .nameignore

a1() {
	local a2="$1"
	for a3 in "${a0[@]}"; do
		if [[ "$a2" == *"$a3"* ]]; then
			return 0
		fi
	done
	return 1
}

a4() {
	local a5="$1"
	local a6="$2"
	local a7="$3"
	local a8

	while true; do
		if [[ -n "$a7" ]]; then
			a8="${a5}${a6}.${a7}"
		else
			a8="${a5}${a6}"
		fi

		if [[ ! -e "$a8" ]]; then
			echo "$a8"
			return
		fi
		a6=$((a6 + 1))
	done
}

a9() {
	local b0="$1"
	local b1="$2"

	find "$b0" -maxdepth 1 -type f ! -name '.*' | while read -r b2; do
	local b3="${b2##*.}"
	local b4
	local b5
	b4=$(basename "$b2")

	if [[ "$b4" == *.* && "$b4" != .* ]]; then
		b5=$(a4 "${b1}/file" "$file_counter" "$b3")
	else
		b5=$(a4 "${b1}/file" "$file_counter" "")
	fi

	mv "$b2" "$b5"
	file_counter=$((file_counter + 1))

done

find "$b0" -mindepth 1 -maxdepth 1 -type d ! -name '.*' | while read -r b6; do
if a1 "$b6"; then
	continue
fi

local b7
b7=$(a4 "${b1}/" "$dir_counter" "")
mv "$b6" "$b7"
dir_counter=$((dir_counter + 1))

a9 "$b7" "$b7"
done
}

file_counter=0
dir_counter=0

a9 "." "." "$file_counter" "$dir_counter"
