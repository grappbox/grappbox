package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentStatePagerAdapter;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.bugtracker_fragments.BugListFragment;

/**
 * Created by marcw on 17/09/2016.
 */
public class BugListFragmentStatePagerAdapter extends FragmentStatePagerAdapter {
    private Context mContext;
    public BugListFragmentStatePagerAdapter(Context context, FragmentManager fm) {
        super(fm);
        mContext = context;
    }

    @Override
    public Fragment getItem(int position) {
        Fragment newFrag = new BugListFragment();
        Bundle arg = new Bundle();
        arg.putInt(BugListFragment.ARG_LIST_TYPE, position);
        newFrag.setArguments(arg);
        return newFrag;
    }

    @Override
    public int getCount() {
        return 3;
    }

    @Override
    public CharSequence getPageTitle(int position) {
        switch (position){
            case BugListFragment.TYPE_OPEN:
                return mContext.getString(R.string.tab_title_open);
            case BugListFragment.TYPE_CLOSE:
                return mContext.getString(R.string.tab_title_close);
            case BugListFragment.TYPE_YOURS:
                return mContext.getString(R.string.tab_title_yours);
            default:
                return super.getPageTitle(position);
        }
    }
}
