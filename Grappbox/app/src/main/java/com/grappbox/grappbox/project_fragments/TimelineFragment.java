package com.grappbox.grappbox.project_fragments;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentStatePagerAdapter;
import android.support.v4.view.ViewPager;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.TimelineFragmentStatePagerAdapter;

/**
 * A simple {@link Fragment} subclass.
 */
public class TimelineFragment extends Fragment {

    private FragmentStatePagerAdapter mPagesAdapter;
    private ViewPager mViewPager;

    public TimelineFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_timeline, container, false);
        mViewPager = (ViewPager) v.findViewById(R.id.viewPager);

        mPagesAdapter = new TimelineFragmentStatePagerAdapter(getActivity(), getActivity().getSupportFragmentManager());
        mViewPager.setAdapter(mPagesAdapter);
        mViewPager.setOffscreenPageLimit(2);

        return v;
    }

}
