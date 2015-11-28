#include "Body/BodyUserSettings.h"

BodyUserSettings::BodyUserSettings(QWidget *parent) : QWidget(parent)
{
    //First, we create all the requested component to put in the widget
    _mainLayout = new QVBoxLayout(this);
    _personalInformationLayout = new QFormLayout();
    _socialInformationLayout = new QFormLayout();
    _firstname = new QLineEdit(tr("Enter your firstname here"));
    _lastname = new QLineEdit(tr("Enter your lastname here"));
    _birthday = new QDateTimeEdit();
    _email = new QLineEdit(tr("Enter your email here"));
    _phone = new QLineEdit(tr("Enter your phone number here"));
    _country = new QComboBox();
    _linkedin = new QLineEdit(tr("Enter your linkedin public URL here"));
    _viadeo = new QLineEdit(tr("Enter your viadeo public URL here"));
    _twitter = new QLineEdit(tr("Enter your twitter public URL here"));
    _btnEditMode = new QPushButton(tr("Pass in edit mode"));

    //We build the country list in the combobox
    for (int i = QLocale::Abkhazian; i <= QLocale::Zulu; i++)
        _country->addItem(QLocale::countryToString(static_cast<QLocale::Country>(i)));

    //We disable all the widgets
    this->SetWidgetActiveState(false);

    //Then we set up the personal and social informations layout
    _personalInformationLayout->addRow(new QLabel(tr("Your firstname")), _firstname);
    _personalInformationLayout->addRow(new QLabel(tr("Your lastname")), _lastname);
    _personalInformationLayout->addRow(new QLabel(tr("Your birthday")), _birthday);
    _personalInformationLayout->addRow(new QLabel(tr("Your email")), _email);
    _personalInformationLayout->addRow(new QLabel(tr("Your phone")), _phone);
    _personalInformationLayout->addRow(new QLabel(tr("Your country")), _country);


    _socialInformationLayout->addRow(new QLabel(tr("Your linkedin")), _linkedin);
    _socialInformationLayout->addRow(new QLabel(tr("Your viadeo")), _viadeo);
    _socialInformationLayout->addRow(new QLabel(tr("Your twitter")), _twitter);

    //We make the basic connexions
    QObject::connect(_btnEditMode, SIGNAL(released()), this, SLOT(passToEditMode()));

    //Finaly, we form the main layout and link it to the widget
    _mainLayout->addWidget(_btnEditMode);
    _mainLayout->addWidget(new QLabel(tr("Personal Informations")));
    _mainLayout->addLayout(_personalInformationLayout);
    _mainLayout->addWidget(new QLabel(tr("Social Informations")));
    _mainLayout->addLayout(_socialInformationLayout);
    this->setLayout(_mainLayout);
}

BodyUserSettings::~BodyUserSettings()
{

}

void BodyUserSettings::Show(__attribute__((unused)) int ID, MainWindow *mainApp)
{
    _mainApplication = mainApp;
    show();
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
    _email->setEnabled(active);
    _phone->setEnabled(active);
    _country->setEnabled(active);
    _linkedin->setEnabled(active);
    _viadeo->setEnabled(active);
    _twitter->setEnabled(active);
}

void BodyUserSettings::passToEditMode()
{
    //Activate the widgets modifications and change the button text
    QObject::disconnect(_btnEditMode, SIGNAL(released()), this, SLOT(passToEditMode());
    _btnEditMode->setText("Save");
    QObject::connect(_btnEditMode, SIGNAL(released()), this, SLOT(passToStaticMode()));

    this->SetWidgetActiveState(true);
    _btnEditMode->setEnabled(true);
}

void BodyUserSettings::passToStaticMode()
{
    //First disable all the widgets
    this->SetWidgetActiveState(false);
    _btnEditMode->setEnabled(false);

    //Then we have to save all the content thanks to the API

    //Finally, reconnect the button and reactivate it
    QObject::disconnect(_btnEditMode, SIGNAL(released()), this, SLOT(passToStaticMode());
    _btnEditMode->setText(tr("Pass in edit mode"));
    QObject::connect(_btnEditMode, SIGNAL(released()), this, SLOT(passToEditMode()));
    _btnEditMode->setEnabled(true);
}
