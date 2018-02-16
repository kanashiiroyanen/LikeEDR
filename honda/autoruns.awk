BEGIN{
    FS="\t";
    mali=1;
    susp=2;
    lines=0;
}
$1~/^\"/{
    split($1, tmp, "\"");
    print "<tr id=\"regparent\"><td>" tmp[2] "</td></tr>";
    autoruns[NR]="<tr id=\"regparent\"><td>" tmp[2] "</td></tr>";
}
$1!~/^\"/{
    split($1, tmp1, "\"");
    split($2, tmp2, "\"");
    split($3, tmp3, "\"");
    split($4, tmp4, "\"");
    print "<tr id=\"regchild\"><td>" tmp1[2] "</td><td>" tmp2[2] "</td><td>" tmp3[2] "</td><td>" tmp4[2] "</td></tr>";
    autoruns[NR]="<tr id=\"regchild\"><td>" tmp1[2] "</td><td>" tmp2[2] "</td><td>" tmp3[2] "</td><td>" tmp4[2] "</td></tr>";
    lines+=1;
}
END{
    print "mali=" mali;
    print "susp=" susp;
    print "ALL=" lines;
}
