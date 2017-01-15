package com.grappbox.grappbox.project_fragments;


import android.content.Context;
import android.content.DialogInterface;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.TabLayout;
import android.support.v4.app.Fragment;
import android.support.v4.view.ViewPager;
import android.support.v7.app.AlertDialog;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.DashboardStatePagerAdapter;

import java.util.ArrayList;

/**
 * A simple {@link Fragment} subclass.
 */
public class DashboardFragment extends Fragment {

    private static final String LOG_TAG = DashboardFragment.class.getSimpleName();

    private TabLayout                       mTabLayout;
    private DashboardStatePagerAdapter      mPagerAdapter;
    private ViewPager                       mViewPager;
    private Context                         mContext;
    private String[]                        mTabTitle;
    private FloatingActionButton            mFab;

    public DashboardFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_dashboard, container, false);
        mTabTitle = getResources().getStringArray(R.array.dashboard_tab);
        getId(v);
        setEvent();
        mPagerAdapter.addTab(getContext().getString(R.string.team_occupation_label));
        mPagerAdapter.addTab(getContext().getString(R.string.next_meeting_label));
        mPagerAdapter.addTab(getContext().getString(R.string.stat_label));
        return v;
    }

    private void getId(View v)
    {
        mContext = getContext();
        mViewPager = (ViewPager)v.findViewById(R.id.viewPager);
        mTabLayout = (TabLayout)v.findViewById(R.id.tab_layout);
        mFab = (FloatingActionButton) v.findViewById(R.id.fab);
        mPagerAdapter = new DashboardStatePagerAdapter(getActivity(), getFragmentManager());
        mViewPager.setAdapter(mPagerAdapter);
    }

    private void setEvent()
    {
        mTabLayout.addOnTabSelectedListener(new TabLayout.ViewPagerOnTabSelectedListener(mViewPager) {

            @Override
            public void onTabSelected(TabLayout.Tab tab) {
                super.onTabSelected(tab);
                if (tab.getPosition() == 0)
                    return;
                mViewPager.setCurrentItem(tab.getPosition());
            }
        });

        mFab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                AlertDialog.Builder builder = new AlertDialog.Builder(mContext, R.style.DashboardDialogOverride);
                builder.setTitle(R.string.set_dashboard);

                if (mPagerAdapter == null)
                    return;

                boolean[] checkedTab = new boolean[mTabTitle.length];
                final ArrayList<String> title = new ArrayList<String>();

                for (int i = 0; i < mTabTitle.length; ++i) {
                    checkedTab[i] = false;
                    for (int j = 0; j < mPagerAdapter.getCount(); ++j) {
                        if (mPagerAdapter.getPageTitle(j).equals(mTabTitle[i])) {
                            checkedTab[i] = true;
                        }
                    }
                    if (checkedTab[i])
                        title.add(mTabTitle[i]);
                }

                builder.setMultiChoiceItems(getResources().getStringArray(R.array.dashboard_tab), checkedTab, new DialogInterface.OnMultiChoiceClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which, boolean isChecked) {
                        if (isChecked) {
                            boolean already = false;
                            for (String name : title) {
                                if (name.equals(mTabTitle[which]))
                                    already = true;
                            }
                            if (!already)
                                title.add(mTabTitle[which]);
                        } else {
                            for (int i = 0; i < title.size(); ++i ){
                                if (title.get(i).equals(mTabTitle[which])) {
                                    title.remove(i);
                                }
                            }
                        }
                    }
                });

                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        mPagerAdapter.setTab(title);
                        mPagerAdapter.notifyDataSetChanged();
                    }
                });
                builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.show();
            }
        });
    }

}
