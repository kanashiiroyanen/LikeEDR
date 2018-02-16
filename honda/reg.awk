BEGIN{
    FS="\t";
    mali=1;
    susp=2;
    lines=0;
    mulsw=0;
    lines2=0;
}
$1~/^\[/{
    lines+=1;
    tmp1=substr($1, 2, length($1)-2);
    print "<tr id=\"regparent\"><td>" tmp1 "</td></tr>";
    reg[lines]="<tr id=\"regparent\"><td>" tmp1 "</td></tr>";
}
$1~/=/{
    lines+=1;
    lines2+=1;
    split($1, tmp2, "=");
    sub(/\"/, "", tmp2[1]);
    sub(/\"/, "", tmp2[2]);
    if(tmp2[2]~/\\/){
        sub(/\\/, "<br/>", tmp2[2]);
        print "<tr id=\"regchild\"><td>" tmp2[1] "</td><td>" tmp2[2] ;
        reg[lines]="<tr id=\"regchild\"><td>" tmp2[1] "</td><td>" tmp2[2] ;
        mulsw=1;
        mul1st=1;
    } else {
        print "<tr id=\"regchild\"><td>" tmp2[1] "</td><td>" tmp2[2] "</td></tr>";
        reg[lines]="<tr id=\"regchild\"><td>" tmp2[1] "</td><td>" tmp2[2] "</td></tr>";
    }
}
{
    if(mulsw==1 && mul1st==0) {
        tmp3=$0;
        sub(/  /, "", tmp3);
        if(tmp3~/\\/){
            sub(/\\/, "<br/>", tmp3);
            print tmp3;
            reg[lines]+=tmp3 ;
            mulsw=1;
        } else {
            print tmp3 "</td></tr>";
            reg[lines]+=tmp3 "</td></tr>";
            mulsw=0;
        }
    } else {
        mul1st=0;
    }
}
END{
    print "mali=" mali;
    print "susp=" susp;
    print "ALL=" lines2;
}
