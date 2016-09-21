package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentStatePagerAdapter;


/**
 * Created by tan_f on 21/09/2016.
 */

public class TimelineFragmentStatePagerAdapter extends FragmentStatePagerAdapter {

    private Context mContext;

    public TimelineFragmentStatePagerAdapter(Context context, FragmentManager fm) {
        super(fm);
        mContext = context;
    }

    @Override
    public Fragment getItem(int position) {
        return null;
    }

    @Override
    public int getCount() {
        return 2;
    }

    @Override
    public CharSequence getPageTitle(int position) {
        switch (position){
            default:
                return super.getPageTitle(position);
        }
    }

}
