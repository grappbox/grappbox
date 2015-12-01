#include "Body/BodyUserSettings.h"

BodyUserSettings::BodyUserSettings(QWidget *parent) : QWidget(parent)
{
    //First, we create all the requested component to put in the widget
    _dataManager = API::SDataManager::GetCurrentDataConnector();
    _mainLayout = new QVBoxLayout(this);
    _personalInformationLayout = new QFormLayout();
    _socialInformationLayout = new QFormLayout();
    _newPassword = new QFormLayout();
    _avatar = new ImageUploadWidget();
    _firstname = new QLineEdit(tr(PH_FIRSTNAME));
    _lastname = new QLineEdit(tr(PH_LASTNAME));
    _birthday = new QDateTimeEdit();
    _email = new QLineEdit(tr(PH_EMAIL));
    _phone = new QLineEdit(tr(PH_PHONE));
    _country = new QComboBox();
    _linkedin = new QLineEdit(tr(PH_LINKEDIN));
    _viadeo = new QLineEdit(tr(PH_VIADEO));
    _twitter = new QLineEdit(tr(PH_TWITTER));
    _password = new QLineEdit();
    _confirmPassword = new QLineEdit();
    _btnEditMode = new QPushButton(tr("Pass in edit mode"));

    _password->setEchoMode(QLineEdit::Password);
    _confirmPassword->setEchoMode(QLineEdit::Password);
    _email->setEnabled(false);

    //We build the country list in the combobox
    for (int i = QLocale::Abkhazian; i <= QLocale::Zulu; i++)
        _country->addItem(QLocale::countryToString(static_cast<QLocale::Country>(i)));

    //We disable all the widgets
    this->SetWidgetActiveState(false);

    //Then we set up the personal and social informations layout
    _personalInformationLayout->addWidget(_avatar);
    _personalInformationLayout->addRow(new QLabel(tr("Your firstname : ")), _firstname);
    _personalInformationLayout->addRow(new QLabel(tr("Your lastname : ")), _lastname);
    _personalInformationLayout->addRow(new QLabel(tr("Your birthday : ")), _birthday);
    _personalInformationLayout->addRow(new QLabel(tr("Your email : ")), _email);
    _personalInformationLayout->addRow(new QLabel(tr("Your phone : ")), _phone);
    _personalInformationLayout->addRow(new QLabel(tr("Your country : ")), _country);


    _socialInformationLayout->addRow(new QLabel(tr("Your linkedin : ")), _linkedin);
    _socialInformationLayout->addRow(new QLabel(tr("Your viadeo : ")), _viadeo);
    _socialInformationLayout->addRow(new QLabel(tr("Your twitter : ")), _twitter);

    _newPassword->addRow(new QLabel(tr("New password : ")), _password);
    _newPassword->addRow(new QLabel(tr("Confirm new password : ")), _confirmPassword);

    //We make the basic connexions
    QObject::connect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToEditMode()));

    //Finaly, we form the main layout and link it to the widget
    _mainLayout->addWidget(_btnEditMode);
    _mainLayout->addWidget(new QLabel(tr("<h2>Personal Informations</h2>")));
    _mainLayout->addLayout(_personalInformationLayout);
    _mainLayout->addWidget(new QLabel(tr("<h2>Social Informations</h2>")));
    _mainLayout->addLayout(_socialInformationLayout);
    _mainLayout->addWidget(new QLabel(tr("<h2>New password (fill only to modify it)</h2>")));
    _mainLayout->addLayout(_newPassword);
    this->setLayout(_mainLayout);
}

BodyUserSettings::~BodyUserSettings()
{

}

void BodyUserSettings::Show(int ID, MainWindow *mainApp)
{
    QVector<QString> userData;

    qDebug(API::SDataManager::GetDataManager()->GetToken().toStdString().c_str());
    userData.append(API::SDataManager::GetDataManager()->GetToken());
    _mainApplication = mainApp;
    _dataManager->Get(API::DP_USER_DATA, API::GR_USER_SETTINGS, userData,this, "GetUserData", "Failure");
    _id = ID;
}

void BodyUserSettings::GetUserData(int UNUSED id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject json = doc.object();
    QJsonObject birthdayJson = json["birthday"].toObject();
    QStringList date(birthdayJson["date"].toString().split(QChar(' ')));
    QPixmap avatar;
    int idCountry;

    if (!json["first_name"].isNull())
        _firstname->setText(json["first_name"].toString());
    if (!json["last_name"].isNull())
        _lastname->setText(json["last_name"].toString());
    if (!json["email"].isNull())
        _email->setText(json["email"].toString());
    if (!json["phone"].isNull())
        _phone->setText(json["phone"].toString());
    if (!json["country"].isNull())
    {
        idCountry = _country->findData(json["country"].toString());
        if (idCountry >= 0)
            _country->setCurrentIndex(idCountry);
        else
        {
            _country->addItem(json["country"].toString());
            idCountry = _country->findData(json["country"].toString());
            _country->setCurrentIndex(idCountry);
        }
    }
    if (!json["linkedin"].isNull())
        _linkedin->setText(json["linkedin"].toString());
    if (!json["viadeo"].isNull())
        _viadeo->setText(json["viadeo"].toString());
    if (!json["twitter"].isNull())
        _twitter->setText(json["twitter"].toString());
    if (!birthdayJson["date"].isNull())
    {
        _birthday->setDate(QDate::fromString(date[0], QString("yyyy-MM-dd")));
        _birthday->setTime(QTime::fromString(date[1], QString("hh:mm:ss")));
    }
    if (!json["avatar"].isNull())
    {
        avatar.loadFromData(QByteArray(json["avatar"].toString().toStdString().c_str()));
        _avatar->setImage(avatar);
    }
    emit OnLoadingDone(_id);
}

void BodyUserSettings::Failure(int UNUSED id, QByteArray UNUSED data)
{
    QMessageBox::critical(this, "Connexion Error", "Failure to retreive data from internet");
    qDebug() << data;
}

void BodyUserSettings::Hide()
{
    hide();
}

void BodyUserSettings::SetWidgetActiveState(bool active)
{
    _firstname->setEnabled(active);
    _lastname->setEnabled(active);
    _birthday->setEnabled(active);
    _phone->setEnabled(active);
    _country->setEnabled(active);
    _linkedin->setEnabled(active);
    _viadeo->setEnabled(active);
    _twitter->setEnabled(active);
    _avatar->setEnabled(active);
    _password->setEnabled(active);
    _confirmPassword->setEnabled(active);
}

void BodyUserSettings::PassToEditMode()
{
    //Activate the widgets modifications and change the button text
    QObject::disconnect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToEditMode()));
    _btnEditMode->setText("Save");
    QObject::connect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToStaticMode()));

    this->SetWidgetActiveState(true);
    _btnEditMode->setEnabled(true);
}

void BodyUserSettings::PassToStaticMode()
{
    QVector<QString> data;
    QString avatar = "";
    uchar *avatarBits;

    //First disable all the widgets
    this->SetWidgetActiveState(false);
    _btnEditMode->setEnabled(false);

    if (_avatar->isImageFromComputer())
    {
        avatarBits = _avatar->getImage().toImage().bits();
        while (*avatarBits)
        {
            avatar.append(QChar(*avatarBits));
            ++avatarBits;
        }
    }

    //Then we have to save all the content thanks to the API
    data.append((_firstname->text() != tr(PH_FIRSTNAME)) ? _firstname->text() : "");
    data.append((_lastname->text() != tr(PH_LASTNAME)) ? _lastname->text() : "");
    data.append(_birthday->dateTime().toString("yyyy-MM-dd hh:mm:ss"));
    data.append(_avatar->isImageFromComputer() ? avatar : "");
    data.append((_phone->text() != tr(PH_PHONE)) ? _phone->text() : "");
    data.append(_country->currentText());
    data.append((_linkedin->text() != tr(PH_LINKEDIN)) ? _linkedin->text() : "");
    data.append((_viadeo->text() != tr(PH_VIADEO)) ? _viadeo->text() : "");
    data.append((_twitter->text() != tr(PH_TWITTER)) ? _twitter->text() : "");
    if (_password->text() != "")
    {
       if (_password->text() != _confirmPassword->text())
       {
           QMessageBox::critical(this, "Password error", "The password and the confirmation fields are different.");
           this->SetWidgetActiveState(true);
           _btnEditMode->setEnabled(true);
           return;
       }
       data.append(_password->text());
    }
    else
        data.append("");
    data.append(API::SDataManager::GetDataManager()->GetToken());
    _dataManager->Put(API::DP_USER_DATA, API::PUTR_UserSettings, data, this, "SaveUserData", "Failure");
}

void BodyUserSettings::SaveUserData(int, QByteArray)
{
    //Finally, reconnect the button and reactivate it
    QObject::disconnect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToStaticMode()));
    _btnEditMode->setText(tr("Pass in edit mode"));
    QObject::connect(_btnEditMode, SIGNAL(released()), this, SLOT(PassToEditMode()));
    _btnEditMode->setEnabled(true);
}
