package com.grappbox.grappbox.grappbox.Dashboard;

import android.content.ContentValues;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.widget.SwipeRefreshLayout;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;
import android.widget.SimpleAdapter;

import com.grappbox.grappbox.grappbox.Model.LoadingFragment;
import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;


public class GlobalProgressFragment extends LoadingFragment {

    private GlobalProgressFragment _context = this;
    private View _view;
    private ListView _projectList;
    private List<ContentValues> _value = null;
    private SwipeRefreshLayout _swiper;
    public SwipeRefreshLayout.OnRefreshListener _refresher;

    @Override
    public void onCreate(Bundle savedInstanceBundle)
    {
        super.onCreate(savedInstanceBundle);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _view = inflater.inflate(R.layout.fragment_global_progress, container, false);
        _swiper = (SwipeRefreshLayout) _view.findViewById(R.id.pull_refresher);
        startLoading(_view, R.id.loader, _swiper);
        if (_value == null) {
            _projectList = (ListView)_view.findViewById(R.id.list_global_progress);
            APIRequestGlobalProgress api = new APIRequestGlobalProgress(_context);
            api.execute();
        } else {
            createContentView(_value);
        }
        _refresher = new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {
                APIRequestGlobalProgress api = new APIRequestGlobalProgress(_context);
                api.SetRefreshSwiper(_swiper);
                api.execute();
            }
        };
        _swiper.setOnRefreshListener(_refresher);
        return _view;
    }

    private String getValueContent(String item)
    {
        final String notSpecified = getResources().getString(R.string.data_not_specified);
        if (item.equals("null") || item.length() == 0)
        {
            return notSpecified;
        }
        return item;
    }

    public void createContentView(List<ContentValues> values)
    {
        ArrayList<HashMap<String, String>> listProjectProgress = new ArrayList<HashMap<String, String>>();

        _value = values;
        for (ContentValues item : _value){
            HashMap<String, String> map = new HashMap<String, String>();
            map.put("project_name", getValueContent(item.get("project_name").toString()));
            map.put("project_description", "Description : " + getValueContent(item.get("project_description").toString()));
            map.put("client_telephone_contact", "Contact Phone : " + getValueContent(item.get("project_phone").toString()));
            map.put("client_company", getValueContent(item.get("project_company").toString()));
            map.put("client_contact_mail", "Mail : " + getValueContent(item.get("contact_mail").toString()));
            map.put("client_contact_facebook", "Facebook : " + getValueContent(item.get("facebook").toString()));
            map.put("client_contact_twitter", "Twitter : " + getValueContent(item.get("twitter").toString()));
            map.put("project_image", String.valueOf(R.mipmap.icon_launcher));
            listProjectProgress.add(map);
        }

        SimpleAdapter teamAdapter = new SimpleAdapter(_view.getContext(), listProjectProgress, R.layout.item_global_progress,
                new String[] {"project_image", "project_name", "project_description", "client_telephone_contact", "client_company", "client_contact_mail", "client_contact_facebook", "client_contact_twitter"},
                new int[] {R.id.project_image, R.id.project_name, R.id.project_description, R.id.client_telephone_contact, R.id.client_company, R.id.client_contact_mail, R.id.client_contact_facebook, R.id.client_contact_twitter});
        _projectList.setAdapter(teamAdapter);

        endLoading();
    }


}
