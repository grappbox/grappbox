package com.grappbox.grappbox.grappbox;

import android.content.res.Resources;
import android.graphics.Color;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.TextView;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Arkanice on 18/09/2015.
 */
public class TeamOccupationFragment extends Fragment {

    private ListView _TeamList;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState)
    {
        View v = inflater.inflate(R.layout.fragment_team_occupation_, container, false);

        _TeamList = (ListView)v.findViewById(R.id.list_team_occupation);
        ArrayList<HashMap<String, String>> listMemberTeam = new ArrayList<HashMap<String, String>>();

        HashMap<String, String> map = new HashMap<String, String>();
        map.put("name_member", "Roland Hemmer");
        map.put("occupation_state", "Busy");
        map.put("profil_image", String.valueOf(R.drawable.roland_hemmer));
        listMemberTeam.add(map);

        map = new HashMap<String, String>();
        map.put("name_member", "Pierre Feytou");
        map.put("occupation_state", "Free");
        map.put("profil_image", String.valueOf(R.drawable.pierre_feytout));
        listMemberTeam.add(map);

        map = new HashMap<String, String>();
        map.put("name_member", "Allyrian Launois");
        map.put("occupation_state", "Busy");
        map.put("profil_image", String.valueOf(R.drawable.allyriane_launois));
        listMemberTeam.add(map);

        map = new HashMap<String, String>();
        map.put("name_member", "Pierre Hofman");
        map.put("occupation_state", "Busy");
        map.put("profil_image", String.valueOf(R.drawable.pierre_hofman));
        listMemberTeam.add(map);

        map = new HashMap<String, String>();
        map.put("name_member", "Valentin Mougenot");
        map.put("occupation_state", "Busy");
        map.put("profil_image", String.valueOf(R.drawable.valentin_mougenot));
        listMemberTeam.add(map);

        map = new HashMap<String, String>();
        map.put("name_member", "Frédéric Tan");
        map.put("occupation_state", "Free");
        map.put("profil_image", String.valueOf(R.drawable.frederic_tan));
        listMemberTeam.add(map);

        SimpleAdapter teamAdapter = new SimpleAdapter(v.getContext(), listMemberTeam, R.layout.team_occupation_item,
                new String[] {"profil_image", "name_member", "occupation_state"}, new int[] {R.id.profil_image, R.id.name_member, R.id.occupation_state});
        _TeamList.setAdapter(teamAdapter);

        return v;
    }
}
