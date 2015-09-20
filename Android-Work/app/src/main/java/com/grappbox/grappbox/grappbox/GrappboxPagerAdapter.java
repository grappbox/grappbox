package com.grappbox.grappbox.grappbox;

import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;

import java.util.List;

/**
 * Created by Arkanice on 18/09/2015.
 */
public class GrappboxPagerAdapter extends FragmentPagerAdapter
{
    private final List<Fragment> _Fragments;

    public GrappboxPagerAdapter(FragmentManager fm, List<Fragment> fragments)
    {
        super(fm);
        this._Fragments = fragments;
    }

    @Override
    public Fragment getItem(int position)
    {
        return (this._Fragments.get(position));
    }

    @Override
    public int getCount()
    {
        return (this._Fragments.size());
    }
}
