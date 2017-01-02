package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentStatePagerAdapter;

import com.grappbox.grappbox.dashboard_fragment.NextMeetingFragment;
import com.grappbox.grappbox.dashboard_fragment.TeamOccupationFragment;
import com.grappbox.grappbox.statistic_fragment.StatisticAdvancementFragment;
import com.grappbox.grappbox.statistic_fragment.StatisticListFragment;

/**
 * Created by tan_f on 21/12/2016.
 */

public class DashboardStatePagerAdapter extends FragmentStatePagerAdapter {

    private static final int TEAM_OCCUPATION = 0;
    private static final int NEXT_MEETING = 1;
    private static final int STAT = 2;

    private Context mContext;

    public DashboardStatePagerAdapter(Context context, FragmentManager fm) {
        super(fm);
        mContext = context;
    }

    @Override
    public Fragment getItem(int position)
    {
        Fragment page = null;

        switch (position){
            case TEAM_OCCUPATION:
                page = new TeamOccupationFragment();
                break;

            case NEXT_MEETING:
                page = new NextMeetingFragment();
                break;

            case STAT:
                page = new StatisticListFragment();
                break;
        }
        return page;
    }

    @Override
    public int getCount() {
        return 3;
    }

    @Override
    public CharSequence getPageTitle(int position) {
        switch (position){
            case TEAM_OCCUPATION:
                return "Team occupation";

            case NEXT_MEETING:
                return "Next Meeting";

            case STAT:
                return "Statistic";
        }
        return "";
    }
}
