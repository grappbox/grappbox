package com.grappbox.grappbox.grappbox.BugTracker;

import android.annotation.TargetApi;
import android.content.Context;
import android.os.Build;
import android.util.AttributeSet;
import android.widget.CheckBox;

/**
 * Created by wieser_m on 19/02/2016.
 */
public class BugIdCheckbox extends CheckBox{
    private String  _storedId;

    public BugIdCheckbox(Context context) {
        super(context);
    }

    public BugIdCheckbox(Context context, AttributeSet attrs) {
        super(context, attrs);
    }

    public BugIdCheckbox(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
    }

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public BugIdCheckbox(Context context, AttributeSet attrs, int defStyleAttr, int defStyleRes) {
        super(context, attrs, defStyleAttr, defStyleRes);
    }

    public void SetId(String id)
    {
        _storedId = id;
    }

    public String GetStoredId()
    {
        return _storedId;
    }
}
