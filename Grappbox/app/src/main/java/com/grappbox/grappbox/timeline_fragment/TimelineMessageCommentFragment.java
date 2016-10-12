package com.grappbox.grappbox.timeline_fragment;

import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.TimelineMessageCommentAdapter;
import com.grappbox.grappbox.model.TimelineModel;

public class TimelineMessageCommentFragment extends Fragment {

    private RecyclerView mRecycler;
    private TimelineMessageCommentAdapter mAdapter;
    private TextView    mTimelineMessage;

    public TimelineMessageCommentFragment(){
        // Required empty public constructor
    }

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        TimelineModel model = getActivity().getIntent().getParcelableExtra(TimelineMessageCommentActivity.EXTRA_TIMELINE_MODEL);
        View view = inflater.inflate(R.layout.timeline_message_comment, container, false);
        mRecycler = (RecyclerView) view.findViewById(R.id.scrollable_content);
        mAdapter = new TimelineMessageCommentAdapter(getActivity());
        mAdapter.setTimelineModel(model);
        mRecycler.setAdapter(mAdapter);
        mRecycler.setLayoutManager(new LinearLayoutManager(getActivity()));
        mTimelineMessage = (TextView) view.findViewById(R.id.message);
        mTimelineMessage.setText(model._message);

        return view;
    }
}
