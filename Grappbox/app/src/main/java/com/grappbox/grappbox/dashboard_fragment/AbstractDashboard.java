package com.grappbox.grappbox.dashboard_fragment;

import android.support.v4.app.Fragment;

/**
 * Created by Arka on 15/01/2017.
 */

public abstract class AbstractDashboard extends Fragment {
    String mTitle = "";

    public void setTitle(String title) {
        mTitle = title;
    }

    public String getTitle() { return mTitle; }
}
