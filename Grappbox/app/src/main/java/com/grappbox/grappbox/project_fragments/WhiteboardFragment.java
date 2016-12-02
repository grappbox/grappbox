package com.grappbox.grappbox.project_fragments;


import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ListView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.WhiteboardDrawingActivity;
import com.grappbox.grappbox.adapter.WhiteboardListAdapter;
import com.grappbox.grappbox.model.WhiteboardModel;
import com.grappbox.grappbox.receiver.WhiteboardListReceiver;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;
import com.grappbox.grappbox.sync.GrappboxWhiteboardJIT;

import java.util.ArrayList;
import java.util.List;

/**
 * A simple {@link Fragment} subclass.
 */
public class WhiteboardFragment extends Fragment implements WhiteboardListReceiver.Callback {
    private WhiteboardListReceiver mReceiver;
    private ListView mWhiteboardList;
    private WhiteboardListAdapter mAdapter;

    public WhiteboardFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_whiteboard, container, false);
        mWhiteboardList = (ListView) v.findViewById(R.id.whiteboard_list);
        mReceiver = new WhiteboardListReceiver(this);
        mAdapter = new WhiteboardListAdapter(getActivity(), new ArrayList<WhiteboardModel>());
        mWhiteboardList.setAdapter(mAdapter);
        mWhiteboardList.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                WhiteboardModel whiteboard = mAdapter.getItem(position);
                if (whiteboard == null)
                    return;
                Intent launchWhiteboard = new Intent(getActivity(), WhiteboardDrawingActivity.class);
                launchWhiteboard.putExtra(WhiteboardDrawingActivity.EXTRA_WHITEBOARD_ID, whiteboard.grappboxId);
                getActivity().startActivity(launchWhiteboard);
            }
        });
        Intent load = new Intent(getActivity(), GrappboxWhiteboardJIT.class);
        load.setAction(GrappboxWhiteboardJIT.ACTION_GET_LIST);
        load.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
        load.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mReceiver);
        Log.e("Test", "startService");
        getActivity().startService(load);
        return v;
    }

    @Override
    public void onListReceived(List<WhiteboardModel> models) {
        Log.e("TEST", "onListReceived");
        mAdapter.clear();
        mAdapter.addAll(models);
    }
}
