package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentStatePagerAdapter;

import com.grappbox.grappbox.statistic_fragment.StatisticAdvancementFragment;
import com.grappbox.grappbox.statistic_fragment.StatisticListFragment;

/**
 * Created by tan_f on 21/12/2016.
 */

public class DashboardStatePagerAdapter extends FragmentStatePagerAdapter {

    private Context mContext;

    public DashboardStatePagerAdapter(Context context, FragmentManager fm) {
        super(fm);
        mContext = context;
    }

    @Override
    public Fragment getItem(int position)
    {
        Fragment page;
        page = new StatisticListFragment();
        return page;
    }

    @Override
    public int getCount() {
        return 1;
    }

    @Override
    public CharSequence getPageTitle(int position) {
        return "title";
    }
}
