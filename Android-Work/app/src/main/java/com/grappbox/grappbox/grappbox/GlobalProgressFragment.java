package com.grappbox.grappbox.grappbox;

import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;
import android.widget.SimpleAdapter;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Arkanice on 18/09/2015.
 */
public class GlobalProgressFragment  extends Fragment {

    private ListView _ListGlobalProgess;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState)
    {
        View v;

        v = inflater.inflate(R.layout.fragment_global_progress, container, false);
        _ListGlobalProgess = (ListView)v.findViewById(R.id.list_progress);
        ArrayList<HashMap<String, String>> listNextMeeting = new ArrayList<HashMap<String, String>>();

        HashMap<String, String> map = new HashMap<String, String>();
        map.put("project_image", String.valueOf(R.drawable.game_sphere));
        map.put("project_name", "Game Sphere");
        map.put("project_client", "Ninvento");
        map.put("client_telephone_contact", "+(1)3-81-21-34");
        map.put("client_mail_contact", "contact@ninvento.com");
        map.put("project_task_progress", "31/42");
        map.put("project_waiting_progress", "4");
        map.put("project_problem_progress", "3");
        listNextMeeting.add(map);

        map = new HashMap<String, String>();
        map.put("project_image", String.valueOf(R.drawable.game_sphere));
        map.put("project_name", "Game Sphere");
        map.put("project_client", "Ninvento");
        map.put("client_telephone_contact", "+(1)3-81-21-34");
        map.put("client_mail_contact", "contact@ninvento.com");
        map.put("project_task_progress", "31/42");
        map.put("project_waiting_progress", "4");
        map.put("project_problem_progress", "3");
        listNextMeeting.add(map);

        map = new HashMap<String, String>();
        map.put("project_image", String.valueOf(R.drawable.game_sphere));
        map.put("project_name", "Game Sphere");
        map.put("project_client", "Ninvento");
        map.put("client_telephone_contact", "+(1)3-81-21-34");
        map.put("client_mail_contact", "contact@ninvento.com");
        map.put("project_task_progress", "31/42");
        map.put("project_waiting_progress", "4");
        map.put("project_problem_progress", "3");
        listNextMeeting.add(map);

        map = new HashMap<String, String>();
        map.put("project_image", String.valueOf(R.drawable.game_sphere));
        map.put("project_name", "Game Sphere");
        map.put("project_client", "Ninvento");
        map.put("client_telephone_contact", "+(1)3-81-21-34");
        map.put("client_mail_contact", "contact@ninvento.com");
        map.put("project_task_progress", "31/42");
        map.put("project_waiting_progress", "4");
        map.put("project_problem_progress", "3");
        listNextMeeting.add(map);

        SimpleAdapter meetingAdapter = new SimpleAdapter(v.getContext(), listNextMeeting, R.layout.global_project_progress_item,
                new String[] {"project_image", "project_name", "project_client", "client_telephone_contact", "client_mail_contact", "project_task_progress", "project_waiting_progress", "project_problem_progress"},
                new int[] {R.id.profil_image, R.id.project_name, R.id.project_client, R.id.client_telephone_contact, R.id.client_mail_contact, R.id.project_task_progress, R.id.project_waiting_progress, R.id.project_problem_progress});
        _ListGlobalProgess.setAdapter(meetingAdapter);
        return v;
    }
}
