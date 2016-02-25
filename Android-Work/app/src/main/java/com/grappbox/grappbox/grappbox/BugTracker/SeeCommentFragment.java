package com.grappbox.grappbox.grappbox.BugTracker;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.Button;
import android.widget.ListView;

import com.grappbox.grappbox.grappbox.R;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.List;

/**
 * A simple {@link Fragment} subclass.
 */
public class SeeCommentFragment extends Fragment {
    private BugCommentAdapter _adapter;

    public SeeCommentFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_see_comment, container, false);
        ListView commentList = (ListView) v.findViewById(R.id.lv_comment);
        Button btnNew = (Button) v.findViewById(R.id.btn_comment);

        _adapter = new BugCommentAdapter(getContext(), R.layout.li_bug_comment);
        commentList.setAdapter(_adapter);

        GetBugCommentTask task = new GetBugCommentTask(getActivity(), new OnTaskListener() {
            @Override
            public void OnTaskEnd(boolean isErrorOccured, String... params) {
                if (isErrorOccured || params.length < 1)
                    return;
                try {
                    JSONObject data = new JSONObject(params[0]);
                    JSONArray array = data.getJSONArray("array");

                    for (int i = 0; i < array.length(); ++i)
                        _adapter.add(new BugCommentEntity(array.getJSONObject(i)));
                } catch (JSONException e) {
                    e.printStackTrace();
                }

            }
        });
        task.execute(((EditBugActivity)getActivity()).GetModel().GetId());

        commentList.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                //TODO : Comment choice
            }
        });

        btnNew.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                //TODO : start comment writing
            }
        });
        return v;
    }

}
