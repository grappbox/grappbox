package com.grappbox.grappbox.grappbox;

import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.view.ViewPager;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import java.util.List;
import java.util.Locale;
import java.util.Vector;

public class DashboardFragment extends Fragment {

    public static final String TAG = DashboardFragment.class.getSimpleName();
    private SectionsPagerAdapter _SectionPagerAdapter;
    private ViewPager           _viewPager;

    @Override
    public void onCreate(Bundle savedInstanceBundle)
    {
        super.onCreate(savedInstanceBundle);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View rootView = inflater.inflate(R.layout.fragment_dashboard, container, false);

        List<Fragment> fragment = new Vector<Fragment>();

        fragment.add(Fragment.instantiate(getActivity(), TeamOccupationFragment.class.getName()));
        fragment.add(Fragment.instantiate(getActivity(), NextMeetingFragment.class.getName()));
        fragment.add(Fragment.instantiate(getActivity(), GlobalProgressFragment.class.getName()));

        _SectionPagerAdapter = new SectionsPagerAdapter(super.getChildFragmentManager(), fragment);
        _viewPager = (ViewPager)rootView.findViewById(R.id.pager);
        _viewPager.setAdapter(_SectionPagerAdapter);
        return rootView;
    }

    public class SectionsPagerAdapter extends FragmentPagerAdapter {

        private List<Fragment>  _fragments = null;

        public SectionsPagerAdapter(FragmentManager fm, List<Fragment> fragments) {
            super(fm);
            _fragments = fragments;
        }

        @Override
        public Fragment getItem(int position) {
            return _fragments.get(position);
        }

        @Override
        public int getCount() {
            return _fragments.size();
        }

        @Override
        public CharSequence getPageTitle(int position) {
            Locale l = Locale.getDefault();
            switch (position) {
                default:
                    break;
            }
            return null;
        }
    }
}
