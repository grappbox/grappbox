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

/**
 * Created by wieser_m on 19/01/2016.
 */
public class DeleteFileSecureTask extends AsyncTask<String, Void, String> {
    CloudExplorerFragment _context;
    CloudFileAdapter _adapter;
    String                      _filename;
    FileItem _deletedObject;

    DeleteFileSecureTask(CloudExplorerFragment context, CloudFileAdapter adapter, FileItem object)
    {
        _context = context;
        _adapter = adapter;
        _deletedObject = object;
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 3)
            return null;
        String token = SessionAdapter.getInstance().getToken();
        String projectId = SessionAdapter.getInstance().getCurrentSelectedProject();
        String path = params[0];
        String filePass = params[1];
        String passwordSafe = params[2];
        _filename = _deletedObject.get_filename();
        APIConnectAdapter api = APIConnectAdapter.getInstance();

        assert path != null && passwordSafe != null;
        path = (path.equals("/") ? (path + _filename) : (path.replace(' ', '|') + "," + _filename));
        path = path.replace('/', ',');
        path = path.replace(' ', '|');
        try {
            api.setVersion("V0.2");
            api.startConnection("cloud/filesecured/" + token + "/" + String.valueOf(projectId) + "/" + path + "/" + filePass + (passwordSafe == "" ? "" : "/" + passwordSafe));
            api.setRequestConnection("DELETE");

            return api.getInputSream();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        int responseCode = 500;
        JSONObject json = null;
        JSONObject info = null;

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
            json = new JSONObject(s);
            info = json.getJSONObject("info");
            assert info != null;
            if (!info.getString("return_code").startsWith("1."))
            {
                if (info.getString("return_code").equals("3.7.9"))
                {
                    AlertDialog.Builder builder = new AlertDialog.Builder(_adapter.getContext());

                    builder.setMessage(R.string.password_error);
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
                else
                {
                    AlertDialog.Builder builder = new AlertDialog.Builder(_adapter.getContext());

                    builder.setMessage(_adapter.getContext().getString(R.string.problem_grappbox_server) + _adapter.getContext().getString(R.string.error_code_head) + info.getString("return_code"));
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
            }
            _adapter.remove(_deletedObject);
        } catch (JSONException e) {
            e.printStackTrace();
        }
        super.onPostExecute(s);
    }
}
