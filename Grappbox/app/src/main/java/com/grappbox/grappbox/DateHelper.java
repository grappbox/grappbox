package com.grappbox.grappbox;

import java.util.Calendar;
import java.util.Date;

/**
 * Created by tan_f on 03/11/2016.
 */

public class DateHelper {

    public static boolean sameDate(Calendar cal1, Calendar cal2){
        return (cal1.get(Calendar.MONTH) == cal2.get(Calendar.MONTH)
                && cal1.get(Calendar.YEAR) == cal2.get(Calendar.YEAR)
                && cal1.get(Calendar.DAY_OF_MONTH) == cal2.get(Calendar.DAY_OF_MONTH));
    }

    public static boolean sameDate(Calendar cal1, Date date){
        Calendar cal2 = Calendar.getInstance();
        cal2.setTime(date);
        return (cal1.get(Calendar.MONTH) == cal2.get(Calendar.MONTH)
                && cal1.get(Calendar.YEAR) == cal2.get(Calendar.YEAR)
                && cal1.get(Calendar.DAY_OF_MONTH) == cal2.get(Calendar.DAY_OF_MONTH));
    }


}
