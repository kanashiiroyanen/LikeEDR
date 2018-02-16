BEGIN{
    mali=1;
    susp=2;
    lines=0;
    lines2=0;
}
$1~/^C\:\\/{
    lines+=1;
    split($0, tmp1, "のディレクトリ");
    tmp2=substr(tmp1[1], 2, length(tmp1[1])-2);
    print "<tr id=\"dirparent\"><td>" tmp2 "</td></tr>";
    dirs[lines]="<tr id=\"regparent\"><td>" tmp2 "</td></tr>";
}
$1~/^20[0-9]+\//{
    lines+=1;
    lines2+=1;
    print "<tr id=\"dirchild\"><td>" $1 " " $2 "</td><td>" $3 "</td><td>" $4 "</td></tr>";
    system("");
    dirs[lines]="<tr id=\"dirchild\"><td>" $1 " " $2 "</td><td>" $3 "</td><td>" $4 "</td></tr>";
}
{
    system("");
}
END{
    print "mali=" mali;
    print "susp=" susp;
    print "ALL=" lines2;
}
