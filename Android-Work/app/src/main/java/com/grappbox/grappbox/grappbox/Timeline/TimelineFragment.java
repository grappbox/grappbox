package com.grappbox.grappbox.grappbox.Timeline;

import android.content.Intent;
import android.os.Bundle;
import android.support.design.widget.TabLayout;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.view.ViewPager;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.BugTracker.BugCreationActivity;
import com.grappbox.grappbox.grappbox.R;

import java.util.List;
import java.util.Vector;

/**
 * Created by tan_f on 15/02/2016.
 */
public class TimelineFragment extends Fragment {

    private TimelinePagerAdapter _SectionPagerAdapter;
    private TimelineListFragment _internalTimeline;
    private TimelineListFragment _customerTimeline;
    private ViewPager _viewPager;
    private TabLayout _tabLayout;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View _rootView = inflater.inflate(R.layout.fragment_timeline, container, false);

        List<Fragment> fragment = new Vector<Fragment>();

        _internalTimeline = (TimelineListFragment) Fragment.instantiate(getActivity(), TimelineListFragment.class.getName());
        _internalTimeline.setContext(this);
        _customerTimeline = (TimelineListFragment) Fragment.instantiate(getActivity(), TimelineListFragment.class.getName());
        _customerTimeline.setContext(this);
        fragment.add(_internalTimeline);
        fragment.add(_customerTimeline);

        _SectionPagerAdapter = new TimelinePagerAdapter(super.getChildFragmentManager(), fragment);
        _viewPager = (ViewPager)_rootView.findViewById(R.id.pager_timeline);
        _viewPager.setAdapter(_SectionPagerAdapter);
        _tabLayout = (TabLayout) _rootView.findViewById(R.id.sliding_tabs_timeline);
        _tabLayout.setupWithViewPager(_viewPager);

        APIRequestGetTimeline timeline = new APIRequestGetTimeline(this, SessionAdapter.getInstance().getCurrentSelectedProject());
        timeline.execute();
        return _rootView;
    }
    

    public void TimelineConvertToTicketBugtracker(String title, String content)
    {
        Intent intent = new Intent(this.getContext(), TimelineConvertToBugtracker.class);
        intent.putExtra("title", title);
        intent.putExtra("content", content);
        startActivity(intent);
    }

    public void TimelineShowCommentMessage(int idMessage, int idTimeline, String titleMessage, String contentMessage)
    {
        Intent intent = new Intent(getActivity(), TimelineCommentActivity.class);
        Bundle bundle = new Bundle();
        bundle.putInt("idTimeline", idTimeline);
        bundle.putInt("idMessage", idMessage);
        bundle.putString("titleMessage", titleMessage);
        bundle.putString("contentMessage", contentMessage);
        intent.putExtras(bundle);
        startActivity(intent);
    }

    private class TimelinePagerAdapter extends FragmentPagerAdapter {

        private List<Fragment> _Fragments = null;
        private String _TabTitle[] = getResources().getStringArray(R.array.timeline_tab_bar_title);

        public TimelinePagerAdapter(FragmentManager fm, List<Fragment> fragments) {
            super(fm);
            _Fragments = fragments;
        }

        @Override
        public Fragment getItem(int position) {
            return _Fragments.get(position);
        }

        @Override
        public int getCount() {
            return _Fragments.size();
        }

        @Override
        public CharSequence getPageTitle(int position) {
            return _TabTitle[position];
        }
    }

    public void fillContentIntern(int idTimeline)
    {
        _internalTimeline.getTimeline(idTimeline);
    }

    public void fillContentCustomer(int idTimeline)
    {
        _customerTimeline.getTimeline(idTimeline);
    }
}
