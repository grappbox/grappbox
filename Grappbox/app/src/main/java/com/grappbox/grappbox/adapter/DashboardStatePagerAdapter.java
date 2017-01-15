package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentStatePagerAdapter;
import android.view.ViewGroup;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.dashboard_fragment.AbstractDashboard;
import com.grappbox.grappbox.dashboard_fragment.NextMeetingFragment;
import com.grappbox.grappbox.dashboard_fragment.TeamOccupationFragment;
import com.grappbox.grappbox.statistic_fragment.StatisticAdvancementFragment;
import com.grappbox.grappbox.statistic_fragment.StatisticListFragment;

import java.util.ArrayList;

/**
 * Created by tan_f on 21/12/2016.
 */

public class DashboardStatePagerAdapter extends FragmentStatePagerAdapter {

    private ArrayList<String>   mTabTitle;
    private ArrayList<Fragment> mListFragment;
    private Context mContext;

    public DashboardStatePagerAdapter(Context context, FragmentManager fm) {
        super(fm);
        mContext = context;
        mTabTitle = new ArrayList<>();
        mListFragment = new ArrayList<>();
    }

    public void addTab(String title) {
        mTabTitle.add(title);
        AbstractDashboard page = null;
        if (title.equals(mContext.getString(R.string.team_occupation_label))) {
            page = new TeamOccupationFragment();
            page.setTitle(mContext.getString(R.string.team_occupation_label));
        } else if (title.equals(mContext.getString(R.string.next_meeting_label))) {
            page = new NextMeetingFragment();
            page.setTitle(mContext.getString(R.string.next_meeting_label));
        } else if (title.equals(mContext.getString(R.string.stat_label))) {
            page = new StatisticListFragment();
            page.setTitle(mContext.getString(R.string.stat_label));
        }
        mListFragment.add(page);
        notifyDataSetChanged();
    }

    public void setTab(ArrayList<String> titles) {
        mTabTitle.clear();
        mListFragment.clear();
        for (String title : titles) {
            addTab(title);
        }
        notifyDataSetChanged();
    }


    @Override
    public Fragment getItem(int position)
    {
        if (mTabTitle.size() == 0)
            return null;
        return mListFragment.get(position);
    }

    @Override
    public int getItemPosition(Object object) {
        AbstractDashboard fragment = (AbstractDashboard) object;
        String title = fragment.getTitle();
        int position = mTabTitle.indexOf(title);

        if (position >= 0)
            return position;
        else
            return POSITION_NONE;
    }

    @Override
    public int getCount() {
        return mTabTitle.size();
    }

    @Override
    public CharSequence getPageTitle(int position) {
        if (mTabTitle.size() == 0)
            return "";
        return mTabTitle.get(position);
    }
}
