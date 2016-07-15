package com.grappbox.grappbox.grappbox.Dashboard;

import android.content.ContentValues;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.widget.SwipeRefreshLayout;
import android.util.Log;
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

public class TeamOccupationFragment extends LoadingFragment {

    private List<ContentValues> _value = null;
    private TeamOccupationFragment  _context = this;
    private View                _view;
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
        _view = inflater.inflate(R.layout.fragment_team_occupation, container, false);
        _swiper = (SwipeRefreshLayout) _view.findViewById(R.id.pull_refresher);
        if (_value != null) {
            createContentView(_value);
        } else {
            APIRequestTeamOccupation api = new APIRequestTeamOccupation(this);
            api.execute();
            startLoading(_view, R.id.loader, _swiper);
        }

        _refresher = new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {
                APIRequestTeamOccupation api = new APIRequestTeamOccupation(_context);
                api.SetRefreshSwiper(_swiper);
                api.execute();
            }
        };
        _swiper.setOnRefreshListener(_refresher);
        return _view;
    }

    public void createContentView(List<ContentValues> contentValues)
    {
        boolean alreadyHere;
        _value = contentValues;
        ListView TeamList = (ListView)_view.findViewById(R.id.list_team_occupation);
        ArrayList<HashMap<String, String>> listMemberTeam = new ArrayList<HashMap<String, String>>();
        ArrayList<String> nameList = new ArrayList<String>();

        Log.v("Team Occupation", String.valueOf(contentValues.size()));
        for (ContentValues item : contentValues){
            alreadyHere = false;
            String nameMember = item.get("first_name").toString() + " " + item.get("last_name").toString();
            for (String name : nameList) {
                if (name.equals(nameMember)) {
                    alreadyHere = true;
                }
            }
            if (!alreadyHere) {
                HashMap<String, String> map = new HashMap<String, String>();
                map.put("name_member", nameMember);
                map.put("occupation_state", item.get("occupation").toString());
                map.put("profil_image", String.valueOf(R.mipmap.icon_launcher));
                listMemberTeam.add(map);
                nameList.add(nameMember);
            }
        }

        SimpleAdapter teamAdapter = new SimpleAdapter(_view.getContext(), listMemberTeam, R.layout.item_team_occupation,
                new String[] {"profil_image", "name_member", "occupation_state"}, new int[] {R.id.profil_image, R.id.name_member, R.id.occupation_state});
        TeamList.setAdapter(teamAdapter);
        endLoading();
    }


}
