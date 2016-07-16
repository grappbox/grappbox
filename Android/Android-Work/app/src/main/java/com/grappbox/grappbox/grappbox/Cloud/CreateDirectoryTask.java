package com.grappbox.grappbox.grappbox.Cloud;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.os.AsyncTask;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.Comparator;

/**
 * Created by wieser_m on 19/01/2016.
 */
public class CreateDirectoryTask extends AsyncTask<String, Void, String> {

    private CloudFileAdapter        _adapter;
    private CloudExplorerFragment   _context;
    private String                  _filename;

    CreateDirectoryTask(CloudExplorerFragment context, CloudFileAdapter adapter)
    {
        _adapter = adapter;
        _context = context;
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 3)
            return null;
        APIConnectAdapter api = APIConnectAdapter.getInstance();
        JSONObject json = new JSONObject();
        JSONObject data = new JSONObject();
        String path = params[0];
        String name = params[1];
        String pass = params[2];

        assert path != null && name != null && pass != null;
        _filename = name;
        try {
            data.put("token", SessionAdapter.getInstance().getToken());
            data.put("project_id", SessionAdapter.getInstance().getCurrentSelectedProject());
            data.put("path", path);
            data.put("dir_name", name);
            if (pass != "")
                data.put("password", pass);
            json.put("data", data);

            api.setVersion("V0.2");
            api.startConnection("cloud/createdir");
            api.setRequestConnection("POST");
            api.sendJSON(json);
            return api.getInputSream();
        } catch (IOException e) {
            e.printStackTrace();
        } catch (JSONException e) {
            e.printStackTrace();
        }
        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        int responseCode = 500;

        try {
            responseCode = APIConnectAdapter.getInstance().getResponseCode();
        } catch (IOException e) {
            e.printStackTrace();
        }

        if (responseCode < 300) {
            APIConnectAdapter.getInstance().closeConnection();
        }
        else
        {
            AlertDialog.Builder builder = new AlertDialog.Builder(_adapter.getContext());

            builder.setMessage(_adapter.getContext().getString(R.string.problem_grappbox_server) + _adapter.getContext().getString(R.string.error_code_head) + String.valueOf(responseCode));
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    cancel(true);
                }
            });
            builder.create().show();
            if (_context != null) {
                _context.setSafePassword("");
            }
            return;
        }

        try {
            JSONObject json = new JSONObject(s);
            JSONObject infos = json.getJSONObject("info");

            assert infos != null;
            if (!infos.getString("return_code").startsWith("1."))
            {
                AlertDialog.Builder builder = new AlertDialog.Builder(_adapter.getContext());

                builder.setMessage(_adapter.getContext().getString(R.string.problem_grappbox_server) + _adapter.getContext().getString(R.string.error_code_head) + infos.getString("return_code"));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        cancel(true);
                    }
                });
                builder.create().show();
                if (_context != null) {
                    _context.setSafePassword("");
                }
                return;
            }
            FileItem item = new FileItem();
            item.set_filename(_filename);
            item.set_type(FileItem.EFileType.DIR);
            _adapter.add(item);
            _adapter.sort(new Comparator<FileItem>() {
                @Override
                public int compare(FileItem lhs, FileItem rhs) {
                    int ret = 0;
                    if (lhs.get_type() == rhs.get_type())
                    {
                        if (lhs.get_filename().equals("Safe"))
                            return -1;
                        else if (rhs.get_filename().equals("Safe"))
                            return 1;
                        return lhs.get_filename().compareTo(rhs.get_filename());
                    }

                    else if (lhs.get_type() == FileItem.EFileType.DIR && rhs.get_type() == FileItem.EFileType.BACK)
                        return 1;
                    else if (lhs.get_type() == FileItem.EFileType.DIR)
                        return -1;
                    return 1;
                }
            });
        } catch (JSONException e) {
            e.printStackTrace();
        }
        super.onPostExecute(s);
    }

}
