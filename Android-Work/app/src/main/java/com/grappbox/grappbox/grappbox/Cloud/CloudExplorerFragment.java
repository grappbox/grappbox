package com.grappbox.grappbox.grappbox.Cloud;

import android.app.AlertDialog;
import android.app.Dialog;
import android.content.Context;
import android.content.DialogInterface;
import android.net.Uri;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.text.TextUtils;
import android.text.method.PasswordTransformationMethod;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Adapter;
import android.widget.AdapterView;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.Collection;
import java.util.Collections;
import java.util.List;

public class CloudExplorerFragment extends Fragment {
    // the fragment initialization parameters, e.g. ARG_ITEM_NUMBER
    private static final String _APISafeDirectoryPath = "/Safe";
    private String _path;
    private CloudFileAdapter _adapter;
    private GetCloudFileListTask _currentLSTask = null;
    private String _safePassword = "";

    public CloudExplorerFragment() {
        _path = "/";
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getArguments() != null) {

        }
    }

    private void handleSafe(GetCloudFileListTask currentTask)
    {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
        AlertDialog dialog = null;
        _currentLSTask = currentTask;
        EditText passView = new EditText(getActivity());

        passView.setTransformationMethod(new PasswordTransformationMethod());
        passView.setId(R.id.cloudexplorer_safepassword_view);
        builder.setCancelable(true);
        builder.setTitle(R.string.safe_password_question);
        builder.setView(passView);
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener(){

            @Override
            public void onClick(DialogInterface dialog, int which) {
                EditText passview = (EditText)((AlertDialog) dialog).findViewById(R.id.cloudexplorer_safepassword_view);

                _safePassword = passview.getText().toString();
                _currentLSTask.execute(_path, _safePassword);
                _currentLSTask = null;
            }
        });

        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener(){

            @Override
            public void onClick(DialogInterface dialog, int which) {
                _currentLSTask = null;
                dialog.cancel();
            }
        });
        dialog = builder.create();
        dialog.show();
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View view = inflater.inflate(R.layout.fragment_cloud_explorer, container, false);
        ListView list = (ListView) view.findViewById(R.id.cloudexplorer_itemlist);
        CloudFileAdapter adapter = new CloudFileAdapter(getContext(), R.id.cloudexplorer_item_filename);
        _adapter = adapter;
        GetCloudFileListTask task = new GetCloudFileListTask(adapter);
        if (_path.startsWith(_APISafeDirectoryPath) && _safePassword == "")
            handleSafe(task);
        else
            task.execute(_path, _safePassword);
        list.setAdapter(adapter);

        list.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                CloudFileAdapter adapter1 = (CloudFileAdapter) parent.getAdapter();
                FileItem clickedItem = adapter1.getItem(position);

                if (clickedItem.get_type() == FileItem.EFileType.BACK)
                {
                    List<String> list = new ArrayList<>();
                    String[] path = _path.split("/");
                    Collections.addAll(list, path);

                    list.remove(list.size() - 1);
                    _path = TextUtils.join("/", list.toArray());
                    if (_path == "")
                        _path = "/";
                    GetCloudFileListTask task = new GetCloudFileListTask(_adapter);
                    if (_path.startsWith(_APISafeDirectoryPath) && _safePassword == "")
                        handleSafe(task);
                    else
                        task.execute(_path, _safePassword);
                }
                else if (clickedItem.get_type() == FileItem.EFileType.DIR) {
                    if (_path == "/")
                        _path += clickedItem.get_filename();
                    else
                        _path += ("/" + clickedItem.get_filename());
                    GetCloudFileListTask task = new GetCloudFileListTask(_adapter);
                    if (_path.startsWith(_APISafeDirectoryPath) && _safePassword == "")
                        handleSafe(task);
                    else
                        task.execute(_path, _safePassword);
                } else {
                    //TODO : Open dialog to know which action execute (Download or delete)
                    AlertDialog.Builder dialogBuilder = new AlertDialog.Builder(getActivity());
                    dialogBuilder.setItems(R.array.cloudExplorer_fileAction, new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialog, int which) {
                            switch (which)
                            {
                                case 0:
                                    //TODO : API Call Download File
                                    break;
                                case 1:
                                    //TODO : API Call Delete File
                                    break;
                                default:
                                    break;
                            }
                        }
                    });
                    dialogBuilder.create().show();
                }
            }

        });

        return view;
    }

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
    }

    @Override
    public void onDetach() {
        super.onDetach();
    }
}
