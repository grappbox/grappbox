package com.grappbox.grappbox.interfaces;

import com.grappbox.grappbox.model.CalendarDayModel;
import com.grappbox.grappbox.model.CalendarEventModel;

import java.util.Calendar;

/**
 * Created by tan_f on 03/11/2016.
 */

public interface CalendarPickerController {
    void    onDaySelected(CalendarDayModel dayModel);

    void    onEventSelected(CalendarEventModel eventModel);

    void    onScrollToDate(Calendar calendar);
}
