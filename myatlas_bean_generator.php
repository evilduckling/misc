<?php

/**
 * Create :
 *  - a bean file.
 *  - create database creation + requesting fields list
 *
 * @param name=beanName
 * @param attributes=<type>-<name>,...
 * @param package=packageName (optional)
 */
define('BEAN_DIR', '/Users/renaud/AndroidStudioProjects/MyAtlas/app/src/main/java/com/myatlas/models');

// retrieve parameter
$beanName = $attributes = NULL;
foreach($argv as $arg) {
    if(strpos($arg, 'name=') === 0) {
        $beanName = ucfirst(substr($arg, 5));
    }
    if(strpos($arg, 'package=') === 0) {
        $package = substr($arg, 8);
    }
    if(strpos($arg, 'attributes=') === 0) {
        $attributes = explode(',', substr($arg, 11));
    }
}
if(empty($beanName) or empty($attributes)) {
    echo showUsage();
    exit;
}

// Create bean file
if (isset($package)) {
    $file = BEAN_DIR."/$package/$beanName.java";
} else {
    $file = BEAN_DIR."/$beanName.java";
}

$attributesDefinition = '';
$constructor = '';
$attributesGetterSetter = '';
$toStringMethod = '';
$importJsonArray = false;
$importJsonObject = false;

// Generate attr def
foreach($attributes as $attribute) {
    list($type, $name) = explode("-", $attribute);
    // Generate definition
    switch ($type) {
        case "String":
        case "int":
            $attributesDefinition .= '    public '.$type.' '.$name.';
';
            break;
        case "JSONObject":
        case "JSONArray":
            $attributesDefinition .= '    private String '.$name.';
';
            break;
    }
}

// Generate constructor
$constructor = "    public $beanName(";
$first = true;
foreach($attributes as $i => $attribute) {
    list($type, $name) = explode("-", $attribute);
    // Generate definition
    switch ($type) {
        case "String":
        case "int":
            if (!$first) {
                $constructor .= ",";
            }
            $constructor .= '
        ' . $type.' '.$name;
            $first = false;
            break;
    }
}
$constructor .= "\n    ) {";
foreach($attributes as $attribute) {
    list($type, $name) = explode("-", $attribute);
    // Generate definition
    switch ($type) {
        case "String":
        case "int":
            $constructor .= '
        this.' . $name .' = '.$name.';';
            break;
    }
}
$constructor .= "\n    }";

// Generate toString();
$toStringMethod .= '    public String toString() {
        String out = "+- '.$beanName.' -" + BACKCHAR;';
$maxAttrLength = 0;
foreach($attributes as $attribute) {
    list($type, $name) = explode("-", $attribute);
    $maxAttrLength = max($maxAttrLength, strlen($name) + 1);
}
foreach($attributes as $attribute) {
    list($type, $name) = explode("-", $attribute);
    // Generate definition
    $toStringMethod .= '
        out += COLUMN_MARKER + "'.str_pad($name, $maxAttrLength, " ").':" + '.$name.' + BACKCHAR;';
}
$toStringMethod .= '
        out += "+---";
        return out;
    }';

// Generate Getter Setter
foreach($attributes as $attribute) {
    list($type, $name) = explode("-", $attribute);
    // Generate definition
    switch ($type) {
        case "JSONObject":
            $importJsonObject = true;
            $attributesGetterSetter .= '
    public JSONObject getJSONObject'.ucfirst($name).'() {
        if ('.$name.' == null) {
            return new JSONObject();
        }
        try {
            return new JSONObject('.$name.');
        } catch (JSONException e) {
            ErrorHelper.warning("Stored json cannot be parsed." + '.$name.');
            ErrorHelper.warning(e);
            return new JSONObject();
        }
    }

    public void setJSONObject'.ucfirst($name).'(JSONObject '.$name.') {
        this.'.$name.' = '.$name.'.toString();
    }
';
            break;

        case "JSONArray":
            $importJsonArray = true;
            $attributesGetterSetter .= '
    public JSONArray getJSONArray'.ucfirst($name).'() {
        if ('.$name.' == null) {
            return new JSONArray();
        }
        try {
            return new JSONArray('.$name.');
        } catch (JSONException e) {
            ErrorHelper.warning("Stored json cannot be parsed." + '.$name.');
            ErrorHelper.warning(e);
            return new JSONArray();
        }
    }

    public void setJSONArray'.ucfirst($name).'(JSONArray '.$name.') {
        this.'.$name.' = '.$name.'.toString();
    }
';
            break;
    }

    switch ($type) {
        case "JSONObject":
        case "JSONArray":
            $attributesGetterSetter .= '
    public String get'.ucfirst($name).'() {
        return '.$name.';
    }

    public void set'.ucfirst($name).'(String '.$name.') {
        this.'.$name.' = '.$name.';
    }
';
    }
}

// Generate bean file
echo "\n  ->  Create file $file";
$fullPackageName = 'com.myatlas.models';
if (isset($package)) {
    $fullPackageName .= '.'.$package;
}
file_put_contents($file, 'package '.$fullPackageName.';'.
(($importJsonArray or $importJsonObject) ? '

import com.myatlas.helpers.ErrorHelper;
' : '').
($importJsonArray ? '
import org.json.JSONArray;' : '').
(($importJsonArray or $importJsonObject) ? '
import org.json.JSONException;' : '').
($importJsonObject ? '
import org.json.JSONObject;' : '').'

/**
 * '.$beanName.' bean.
 *
 * @author renaud@myatlas.com
 */
public class '.$beanName.' {

    private static final String BACKCHAR = "\n";
    private static final String COLUMN_MARKER = "| ";

'.$attributesDefinition.'
'.$constructor.'
'.$attributesGetterSetter.'
'.$toStringMethod.'

}');

// Generate code to copy/paste
$fields = array();
foreach($attributes as $attribute) {
    list($type, $name) = explode("-", $attribute);
    $fields[] = $name;
}
echo "\n  ->  Copy/paste in db handler";
echo '

    private static String get'.$beanName.'FieldList() {
        return "'.implode(', ', $fields).'";
    }
';

// Generate sql for insert
$sqlInsert = '
    /**
     * Insert a '.$beanName.' list
     */
    synchronized public void add'.$beanName.'List(List<'.$beanName.'> '.lcfirst($beanName).'List) {
        SQLiteDatabase db = this.getWritableDatabase();
        db.beginTransaction();
        try {
            for ('.$beanName.' '.lcfirst($beanName).' : '.lcfirst($beanName).'List) {
                ContentValues values = new ContentValues();';
foreach($attributes as $attribute) {
    list($type, $name) = explode("-", $attribute);
    // Generate definition
    switch ($type) {
        case "String":
        case "int":
            $sqlInsert .= '
                values.put("'.$name.'", '.lcfirst($beanName).'.'.$name.');';
            break;
        case "JSONObject":
        case "JSONArray":
            $sqlInsert .= '
                values.put("'.$name.'", '.lcfirst($beanName).'.get'.ucfirst($name).'());';
            break;
    }
}
$sqlInsert .= '
                Log.v(LOG_TAG, "INSERT INTO " + TABLE_'.strtoupper($beanName).' + " ... ");
                db.insert(TABLE_'.strtoupper($beanName).', null, values);
            }
            db.setTransactionSuccessful();
        } finally {
            db.endTransaction();
        }
        db.close();

        dataVersion++;
    }
';
echo $sqlInsert;
echo "\n";

// Data getters
$sqlGetJSON = '';
$sqlGetArrayBuffer = array();
foreach($attributes as $attribute) {
    list($type, $name) = explode("-", $attribute);
    // Generate definition
    switch ($type) {
        case "String":
            $sqlGetArrayBuffer[] =
'                    cursor.getString(cursor.getColumnIndex("'.$name.'"))';
            break;
        case "int":
            $sqlGetArrayBuffer[] =
'                    cursor.getInt(cursor.getColumnIndex("'.$name.'"))';
            break;
        case "JSONObject":
        case "JSONArray":
            $sqlGetJSON .=
'                '.lcfirst($beanName).'.set'.ucfirst($name).'(cursor.getString(cursor.getColumnIndex("'.$name.'")));
';
            break;
    }
}
echo '
    synchronized public List<'.$beanName.'> getAll'.$beanName.'() {
        List<'.$beanName.'> '.lcfirst($beanName).'List = new ArrayList<>();
        String selectQuery = "SELECT " + get'.$beanName.'FieldList() + " FROM " + TABLE_'.strtoupper($beanName).';
        Log.v(LOG_TAG, selectQuery);
        SQLiteDatabase db = getWritableDatabase();
        Cursor cursor = db.rawQuery(selectQuery, null);
        if (cursor.moveToFirst()) {
            do {
                '.$beanName.' '.lcfirst($beanName).' = new '.$beanName.'(
'.implode(",\n", $sqlGetArrayBuffer).'
                );
'.$sqlGetJSON.
'                '.lcfirst($beanName).'List.add('.lcfirst($beanName).');
            } while (cursor.moveToNext());
        }
        cursor.close();
        db.close();
        return '.lcfirst($beanName).'List;
    }
';

// Generate sql creation
$sqlCreate = '
        db.execSQL("CREATE TABLE " + TABLE_'.strtoupper($beanName).' +
            "(" +';
foreach($attributes as $i => $attribute) {
    list($type, $name) = explode("-", $attribute);
    switch ($type) {
        case "String":
        case "JSONObject":
        case "JSONArray":
            $sqlCreate .= '
            /*TODO check not null*/"'.$name.' TEXT NOT NULL';
            break;
        case "int":
            $sqlCreate .= '
            "'.$name.' INTEGER';
            break;
    }
    if ($i == (count($attributes) - 1)) {
        $sqlCreate .= '" +';
    } else {
        $sqlCreate .= ', " +';
    }
}
$sqlCreate .= '
            ")"
        );
';
echo $sqlCreate;
echo "\n";

// Delete all function
echo '
    synchronized public void deleteAll'.$beanName.'() {
        SQLiteDatabase db = this.getWritableDatabase();
        Log.v(LOG_TAG, "DELETE " + TABLE_'.strtoupper($beanName).');
        db.delete(TABLE_'.strtoupper($beanName).', null, null);
        db.close();

        dataVersion++;
    }

';

function showUsage() {
    return "\nUsage: php myatlas_bean_generator.php name=<beanName> attributes=<Type-name,attributes>\n\n";
}
