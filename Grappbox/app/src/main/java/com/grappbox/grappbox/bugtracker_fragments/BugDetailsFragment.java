package com.grappbox.grappbox.bugtracker_fragments;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.BugDetailAdapter;
import com.grappbox.grappbox.model.BugModel;

/**
 * A simple {@link Fragment} subclass.
 */
public class BugDetailsFragment extends Fragment {
    private RecyclerView mRecycler;
    private BugDetailAdapter mAdapter;
    private TextView mBugDescription;

    public BugDetailsFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        BugModel model = getActivity().getIntent().getParcelableExtra(BugDetailsActivity.EXTRA_BUG_MODEL);
        View v = inflater.inflate(R.layout.bugtracker_details, container, false);
        mRecycler = (RecyclerView) v.findViewById(R.id.scrollable_content);
        mAdapter = new BugDetailAdapter(getActivity());
        mAdapter.setBugModel(model);
        mRecycler.setAdapter(mAdapter);
        mRecycler.setLayoutManager(new LinearLayoutManager(getActivity()));
        mBugDescription = (TextView) v.findViewById(R.id.description);
        mBugDescription.setText(model.desc);
        return v;
    }
}
