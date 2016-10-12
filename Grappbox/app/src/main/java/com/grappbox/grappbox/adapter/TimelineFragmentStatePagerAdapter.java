package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentStatePagerAdapter;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.timeline_fragment.TimelineListFragment;

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
        Fragment page = new TimelineListFragment();
        Bundle args = new Bundle();
        args.putInt(TimelineListFragment.ARG_LIST_TYPE, position);
        page.setArguments(args);
        return page;
    }

    @Override
    public int getCount() {
        return 2;
    }

    @Override
    public CharSequence getPageTitle(int position) {
        switch (position){
            case TimelineListFragment.TIMELINE_TEAM:
                return mContext.getString(R.string.timeline_tab_title_team);

            case TimelineListFragment.TIMELINE_CLIENT:
                return mContext.getString(R.string.timeline_tab_title_client);

            default:
                return super.getPageTitle(position);
        }
    }

}
