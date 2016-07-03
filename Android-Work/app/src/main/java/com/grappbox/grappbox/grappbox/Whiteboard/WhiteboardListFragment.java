package com.grappbox.grappbox.grappbox.Whiteboard;


import android.app.AlertDialog;
import android.content.ContentValues;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.support.design.widget.FloatingActionButton;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.ListView;
import android.widget.SimpleAdapter;

import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Vector;


/**
 * A simple {@link Fragment} subclass.
 */
public class WhiteboardListFragment extends Fragment {

    private View _view;
    private Context _context = this.getContext();
    private WhiteboardListFragment _whiteboard = this;
    private ListView _ListWhiteboard;
    private FloatingActionButton _fab;
    private Vector<String> _titleWhiteboard = new Vector<String>();
    private Vector<String> _idWhiteboard = new Vector<String>();
    private Vector<String> _dateWhiteboard = new Vector<String>();
    private APIRequestAddWhiteboard _apiAdd;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _view = inflater.inflate(R.layout.fragment_whiteboard_list, container, false);

        _fab = (FloatingActionButton) _view.findViewById(R.id.add_whiteboard);
        _fab.setOnClickListener((View v) -> {
            createWhiteboard();
        });
        _fab.hide();


        _apiAdd = new APIRequestAddWhiteboard(this);
        APIRequestGetWhiteboardList api = new APIRequestGetWhiteboardList(this);
        api.execute();
        return _view;
    }

    private void createWhiteboard()
    {
        AlertDialog.Builder addWhiteboardDialogBuilder = new AlertDialog.Builder(this.getActivity());

        addWhiteboardDialogBuilder.setMessage("Whiteboard").setTitle(R.string.add_whiteboard_label);
        EditText whiteboardName = new EditText(this.getContext());
        whiteboardName.setHint("Your whiteboard name");
        LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.MATCH_PARENT);
        whiteboardName.setLayoutParams(lp);
        addWhiteboardDialogBuilder.setView(whiteboardName);
        addWhiteboardDialogBuilder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                _apiAdd.execute(String.valueOf(SessionAdapter.getInstance().getCurrentSelectedProject()), whiteboardName.getText().toString());
            }
        });
        addWhiteboardDialogBuilder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {

            }
        });
        AlertDialog dialog = addWhiteboardDialogBuilder.create();
        dialog.show();
    }

    public void fillList(List<ContentValues> value)
    {
        _ListWhiteboard = (ListView)_view.findViewById(R.id.list_whiteboard);
        ArrayList<HashMap<String, String>> listWhiteboard = new ArrayList<HashMap<String, String>>();

        for (ContentValues item : value)
        {
            HashMap<String, String> map = new HashMap<String, String>();
            map.put("whiteboard_title", item.get("name").toString());
            map.put("whiteboard_create_date", item.get("createdAt").toString().substring(0, 9));
            _idWhiteboard.add(item.get("id").toString());
            _dateWhiteboard.add(item.get("createdAt").toString());
            _titleWhiteboard.add(item.get("name").toString());
            listWhiteboard.add(map);
        }

        SimpleAdapter whiteboardAdapter = new SimpleAdapter(_view.getContext(), listWhiteboard, R.layout.item_list_whiteboard,
                new String[] {"whiteboard_title", "whiteboard_create_date", },
                new int[] {R.id.whiteboard_title, R.id.whiteboard_create_date, });

        _ListWhiteboard.setAdapter(whiteboardAdapter);
        _ListWhiteboard.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                whiteboardAction(_idWhiteboard.get(position), _dateWhiteboard.get(position), _titleWhiteboard.get(position));

            }
        });
        whiteboardAdapter.notifyDataSetChanged();
        _fab.show();
    }

    private void openWhiteboard(String id, String date, String title)
    {
        Intent intent = new Intent(this.getActivity(), WhiteboardActivity.class);
        intent.putExtra("idWhiteboard", id);
        intent.putExtra("createdAt", date);
        intent.putExtra("title", title);
        startActivity(intent);
    }

    private void deleteWhiteboard(String id)
    {
        AlertDialog.Builder whiteboardBuilder = new AlertDialog.Builder(this.getActivity());

        whiteboardBuilder.setTitle(R.string.delete_whiteboard_label);
        whiteboardBuilder.setMessage(R.string.delete_confirm_whiteboard_label);
        whiteboardBuilder.setPositiveButton(R.string.confirm_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                APIRequestDeleteWhiteboard delete = new APIRequestDeleteWhiteboard(_whiteboard);
                delete.execute(id);
            }
        });
        whiteboardBuilder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {

            }
        });
        AlertDialog dialog = whiteboardBuilder.create();
        dialog.show();
    }

    private void whiteboardAction(String id, String date, String title)
    {
        AlertDialog.Builder whiteboardBuilder = new AlertDialog.Builder(this.getActivity());

        whiteboardBuilder.setTitle("Whiteboard");
        whiteboardBuilder.setItems(R.array.whiteboard_action, new DialogInterface.OnClickListener() {

        public void onClick(DialogInterface dialog, int which) {
            switch (which){
                case 0:
                    openWhiteboard(id, date, title);
                    break;

                case 1:
                    deleteWhiteboard(id);
                    break;

                default:
                    break;
            }
        }
    });
        AlertDialog dialog = whiteboardBuilder.create();
        dialog.show();
    }
}
